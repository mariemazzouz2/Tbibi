<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Google\Client;

#[AsCommand(
    name: 'app:google-api-token',
    description: 'Generate a Google API OAuth token',
)]
class GoogleApiTokenCommand extends Command
{
    private $credentialsPath;
    private $tokenPath;

    public function __construct(string $credentialsPath = null, string $tokenPath = null)
    {
        parent::__construct();
        
        $this->credentialsPath = $credentialsPath ?? __DIR__ . '/../../config/google_credentials.json';
        $this->tokenPath = $tokenPath ?? __DIR__ . '/../../var/google_token.json';
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command helps you authenticate with Google API and get an OAuth token for your application');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        try {
            // Check if credentials file exists
            if (!file_exists($this->credentialsPath)) {
                $io->error(sprintf('Google API credentials file not found at: %s', $this->credentialsPath));
                return Command::FAILURE;
            }
            
            $client = new Client();
            $client->setApplicationName('Tbibi Medical Platform');
            $client->setAuthConfig($this->credentialsPath);
            $client->setAccessType('offline');
            $client->setPrompt('consent'); // Force to get refresh token
            $client->setIncludeGrantedScopes(true);
            
            // Set redirect URI explicitly
            $client->setRedirectUri('http://localhost:8080');
            
            // Add scopes needed for Calendar API
            $client->setScopes([
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events',
            ]);
            
            // Display Google Cloud Console instructions first
            $io->section('Before starting: Google Cloud Console Configuration');
            $io->note([
                'Before proceeding, ensure your Google Cloud OAuth credentials include these redirect URIs:',
                '- urn:ietf:wg:oauth:2.0:oob (for command-line authorization)',
                '- http://localhost',
                '- http://localhost:8080',
                'You can add these in Google Cloud Console → APIs & Services → Credentials → Edit OAuth client'
            ]);
            
            if (!$io->confirm('Have you configured these redirect URIs in Google Cloud Console?', false)) {
                $io->warning([
                    'Please configure the redirect URIs in Google Cloud Console first.',
                    'Then run this command again.'
                ]);
                return Command::SUCCESS; // Exit gracefully
            }
            
            // Generate auth URL
            $io->section('Google OAuth Authorization');
            $authUrl = $client->createAuthUrl();
            
            $io->note('Step 1: Copy and paste this URL into your browser:');
            $io->writeln($authUrl);
            $io->newLine();
            $io->note('Step 2: Log in with your Google account and grant the requested permissions');
            $io->note('Step 3: Google will display a code on the screen after authorization');
            $io->note('Step 4: Copy that code and paste it below');
            
            $authCode = $io->ask('Enter the authorization code');
            
            if (empty($authCode)) {
                $io->error('Authorization code cannot be empty.');
                return Command::FAILURE;
            }
            
            // Exchange auth code for access token
            try {
                $io->text('Exchanging authorization code for access token...');
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                
                if (array_key_exists('error', $accessToken)) {
                    $io->error(sprintf('Error fetching access token: %s', $accessToken['error']));
                    $io->error('Description: ' . ($accessToken['error_description'] ?? 'No detailed description available'));
                    $io->warning('Common issues:');
                    $io->listing([
                        'Incorrect or expired authorization code',
                        'Missing redirect URIs in Google Cloud Console',
                        'Mismatched redirect URI between code and request'
                    ]);
                    return Command::FAILURE;
                }
                
                // Save the token to file
                if (!file_exists(dirname($this->tokenPath))) {
                    mkdir(dirname($this->tokenPath), 0700, true);
                }
                
                file_put_contents($this->tokenPath, json_encode($accessToken));
                $io->success(sprintf('Access token has been saved to: %s', $this->tokenPath));
                
                // Display token info
                $io->section('Token Information');
                $io->table(
                    ['Property', 'Value'],
                    [
                        ['Access Token', substr($accessToken['access_token'], 0, 15) . '...'],
                        ['Token Type', $accessToken['token_type'] ?? 'N/A'],
                        ['Expires In', $accessToken['expires_in'] ?? 'N/A'],
                        ['Refresh Token', isset($accessToken['refresh_token']) ? 'Present' : 'Not present'],
                        ['Created', date('Y-m-d H:i:s', $accessToken['created'] ?? time())],
                    ]
                );
                
                if (!isset($accessToken['refresh_token'])) {
                    $io->warning([
                        'No refresh token was received! Your token will expire and require re-authentication.',
                        'To get a refresh token, revoke access in Google Account settings and try again.'
                    ]);
                }
                
                // Test the token with a simple request
                $io->section('Testing API Access');
                $io->text('Making a test request to Google Calendar API...');
                
                $service = new \Google\Service\Calendar($client);
                $calendarList = $service->calendarList->listCalendarList();
                
                $io->success(sprintf('API connection successful! Found %d calendars.', count($calendarList->getItems())));
                $io->success('Google API authentication completed successfully. Your application can now use the Google API.');
                
                return Command::SUCCESS;
            } catch (\Exception $e) {
                $io->error('Error exchanging code for token: ' . $e->getMessage());
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}