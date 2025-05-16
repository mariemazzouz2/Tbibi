<?php

namespace App\Service;

use App\Entity\Consultation;
use App\Enum\TypeConsultation;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Psr\Log\LoggerInterface;

class ConsultationMeetService
{
    private Client $client;
    private ?Calendar $calendarService = null;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private string $credentialsPath;
    private string $tokenPath;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $credentialsPath = null,
        string $tokenPath = null
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        
        // Use default paths if not provided
        $this->credentialsPath = $credentialsPath ?? __DIR__ . '/../../config/google_credentials.json';
        $this->tokenPath = $tokenPath ?? __DIR__ . '/../../var/google_token.json';
        
        // Initialize Google client
        $this->initializeGoogleClient();
    }
    
    /**
     * Initialize the Google API client
     */
    private function initializeGoogleClient(): void
    {
        try {
            // Check if credentials file exists
            if (!file_exists($this->credentialsPath)) {
                $this->logger->error(sprintf('Google API credentials file not found at: %s', $this->credentialsPath));
                throw new \RuntimeException(sprintf('Google API credentials file not found at: %s', $this->credentialsPath));
            }
            
            $this->client = new Client();
            $this->client->setApplicationName('Tbibi Medical Platform');
            $this->client->setAuthConfig($this->credentialsPath);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            $this->client->setScopes([Calendar::CALENDAR, Calendar::CALENDAR_EVENTS]);
            
            // Set the redirect URI explicitly to avoid "missing the required redirect URI" error
            // This URI must be registered in the Google Cloud Console OAuth credentials
            $this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            
            // Load previously authorized token if available
            if (file_exists($this->tokenPath)) {
                $tokenData = file_get_contents($this->tokenPath);
                $accessToken = json_decode($tokenData, true);
                
                if (!$accessToken || json_last_error() !== JSON_ERROR_NONE) {
                    $this->logger->error('Invalid token data in file: ' . $this->tokenPath);
                    throw new \RuntimeException('Invalid token data. Please re-authenticate using the app:google-api-token command.');
                }
                
                $this->client->setAccessToken($accessToken);
            } else {
                $this->logger->warning('No auth token found. Please run the app:google-api-token command to generate one.');
                throw new \RuntimeException('Google API token not found. Please authenticate first using the app:google-api-token command.');
            }
            
            // Refresh token if expired
            if ($this->client->isAccessTokenExpired()) {
                $this->logger->info('Access token is expired. Attempting to refresh...');
                if ($this->client->getRefreshToken()) {
                    try {
                        $this->logger->info('Refreshing token with refresh token...');
                        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                        
                        // Save the refreshed token
                        file_put_contents($this->tokenPath, json_encode($this->client->getAccessToken()));
                        $this->logger->info('Token refreshed and saved successfully.');
                    } catch (\Exception $e) {
                        $this->logger->error('Failed to refresh token: ' . $e->getMessage());
                        throw new \RuntimeException('Failed to refresh Google API token: ' . $e->getMessage() . '. Please re-authenticate using the app:google-api-token command.');
                    }
                } else {
                    $this->logger->warning('Access token expired and no refresh token available. Re-authentication needed.');
                    throw new \RuntimeException('Google API access token expired and no refresh token available. Please re-authenticate using the app:google-api-token command.');
                }
            }
            
            // Create Google Calendar service
            $this->calendarService = new Calendar($this->client);
            
            // Verify API access with a simple request
            try {
                $this->calendarService->calendarList->listCalendarList(['maxResults' => 1]);
                $this->logger->info('Google Calendar API connection verified successfully.');
            } catch (\Exception $e) {
                $this->logger->error('Failed to access Google Calendar API: ' . $e->getMessage());
                throw new \RuntimeException('Failed to access Google Calendar API: ' . $e->getMessage() . '. Check your token permissions.');
            }
        } catch (\Exception $e) {
            $this->logger->error('Google client initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Schedule a Google Meet for a consultation and save the link in the database
     */
    public function scheduleConsultationMeet(Consultation $consultation): ?array
    {
        // Only schedule for online consultations that don't already have a meet link
        if ($consultation->getType() !== TypeConsultation::ONLINE || $consultation->getMeetLink()) {
            return null;
        }
        
        try {
            // Create event details
            $startTime = $consultation->getDateC();
            $endTime = (clone $startTime)->modify('+60 minutes'); // Default 1 hour consultation
            
            // Get patient and doctor names for the meeting title
            $patientName = $consultation->getPatient()->getNom() ?? 'Patient';
            $doctorName = $consultation->getMedecin()->getNom() ?? 'Doctor';
            $meetingTitle = "Medical Consultation: $patientName with Dr. $doctorName";
            
            // Create Google Calendar event with Meet
            $event = new Event([
                'summary' => $meetingTitle,
                'description' => $consultation->getCommentaire() ?? 'Online medical consultation',
                'start' => [
                    'dateTime' => $startTime->format(\DateTimeInterface::RFC3339),
                    'timeZone' => $startTime->getTimezone()->getName(),
                ],
                'end' => [
                    'dateTime' => $endTime->format(\DateTimeInterface::RFC3339),
                    'timeZone' => $endTime->getTimezone()->getName(),
                ],
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => uniqid('meet_', true),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ],
                'attendees' => [
                    ['email' => $consultation->getPatient()->getEmail() ?? 'unknown@example.com'],
                    ['email' => $consultation->getMedecin()->getEmail() ?? 'unknown@example.com'],
                ],
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => 24 * 60], // 1 day before
                        ['method' => 'popup', 'minutes' => 30], // 30 minutes before
                    ],
                ],
            ]);
            
            // Insert the event
            $this->logger->info('Creating Google Calendar event with Meet integration...');
            $event = $this->calendarService->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all', // Send updates to all attendees
            ]);
            
            // Verify the Meet link was created
            $meetLink = $event->getHangoutLink();
            if (empty($meetLink)) {
                $this->logger->warning('No Google Meet link was generated for the event.');
                // Fallback to using the event link
                $meetLink = $event->getHtmlLink() ?? ('https://calendar.google.com/event?eid=' . $event->getId());
            }
            
            // Store the meet link in the consultation entity
            $consultation->setMeetLink($meetLink);
            $this->entityManager->flush();
            
            $this->logger->info('Google Meet scheduled successfully', [
                'event_id' => $event->getId(),
                'meet_link' => $meetLink
            ]);
            
            // Return the meeting details
            return [
                'id' => $event->getId(),
                'summary' => $event->getSummary(),
                'hangout_link' => $meetLink,
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to schedule consultation meet: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cancel a scheduled Google Meet for a consultation
     */
    public function cancelConsultationMeet(Consultation $consultation, string $eventId): bool
    {
        try {
            // Delete the event from Google Calendar
            $this->logger->info('Cancelling Google Calendar event', ['event_id' => $eventId]);
            $this->calendarService->events->delete('primary', $eventId);
            
            // Remove the meet link from the consultation
            $consultation->setMeetLink(null);
            $this->entityManager->flush();
            
            $this->logger->info('Google Meet cancelled successfully', ['event_id' => $eventId]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to cancel consultation meet: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reschedule a Google Meet for a consultation
     */
    public function rescheduleConsultationMeet(Consultation $consultation, string $eventId): ?array
    {
        try {
            // Get the existing event
            $this->logger->info('Getting Google Calendar event for rescheduling', ['event_id' => $eventId]);
            $event = $this->calendarService->events->get('primary', $eventId);
            
            // Update the event times
            $startTime = $consultation->getDateC();
            $endTime = (clone $startTime)->modify('+60 minutes');
            
            $event->setStart(new EventDateTime([
                'dateTime' => $startTime->format(\DateTimeInterface::RFC3339),
                'timeZone' => $startTime->getTimezone()->getName(),
            ]));
            
            $event->setEnd(new EventDateTime([
                'dateTime' => $endTime->format(\DateTimeInterface::RFC3339),
                'timeZone' => $endTime->getTimezone()->getName(),
            ]));
            
            // Update the event
            $this->logger->info('Updating Google Calendar event', ['event_id' => $eventId]);
            $updatedEvent = $this->calendarService->events->update('primary', $eventId, $event, [
                'sendUpdates' => 'all',
            ]);
            
            $this->logger->info('Google Meet rescheduled successfully', ['event_id' => $eventId]);
            
            return [
                'id' => $updatedEvent->getId(),
                'summary' => $updatedEvent->getSummary(),
                'hangout_link' => $updatedEvent->getHangoutLink(),
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to reschedule consultation meet: ' . $e->getMessage());
            return null;
        }
    }
} 