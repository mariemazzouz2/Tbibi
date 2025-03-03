# Google API Integration for Tbibi

This document explains how to set up and test the Google Calendar API integration for creating Google Meet links in online consultations.

## Prerequisites

1. Google Cloud Console account
2. Google API project with Calendar API enabled
3. OAuth credentials configured correctly

## Setup Instructions

### 1. Google Cloud Console Configuration

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Calendar API:
   - Go to "APIs & Services" > "Library"
   - Search for "Google Calendar API"
   - Click on it and then click "Enable"

4. Configure OAuth consent screen:
   - Go to "APIs & Services" > "OAuth consent screen"
   - Choose "External" user type (if not in G Suite)
   - Fill in app name, user support email, and developer contact information
   - Add the following scopes:
     - `https://www.googleapis.com/auth/calendar`
     - `https://www.googleapis.com/auth/calendar.events`
   - Add test users (including the Google account you will use for testing)

5. Create OAuth credentials:
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "OAuth client ID"
   - Choose "Web application" as the application type
   - Give it a name like "Tbibi Medical Platform"
   - **Important**: Add the following authorized redirect URIs:
     - `urn:ietf:wg:oauth:2.0:oob` (for command-line authorization)
     - `http://localhost`
     - `http://localhost:8080`
   - Click "Create"

6. Download the credentials:
   - After creating the client ID, click the download icon (JSON)
   - Save this file as `google_credentials.json` in your project's `config/` directory

### 2. Set Up the Project

1. Make sure you have the Google API client library installed:
   ```bash
   composer require google/apiclient
   ```

2. Create the token directory:
   ```bash
   mkdir -p var/
   chmod 777 var/
   ```

3. Make sure your `config/google_credentials.json` file is in place

### 3. Generate the OAuth Token

Run the following command to generate the OAuth token:

```bash
php bin/console app:google-api-token
```

Follow the instructions displayed by the command:
1. Confirm that you've configured the redirect URIs in Google Cloud Console
2. Open the provided URL in your browser
3. Log in with your Google account and grant the requested permissions
4. Copy the authorization code displayed on the screen
5. Paste the code back into the command prompt

If successful, the command will:
- Save the access token and refresh token to `var/google_token.json`
- Display token information
- Test the API connection

## Testing

You can use the test scripts to verify the Google API integration is working:

### Simple Authentication Test

```bash
php test_google_auth.php
```

This script will verify:
- Your credentials file
- Your token file
- Refresh the token if needed
- Test the API connection

### Full Service Test

```bash
php test_consultation_meet_service.php
```

This script will test:
- Creating a fake consultation
- Scheduling a Google Meet
- Rescheduling the meeting
- Cancelling the meeting

## Troubleshooting

### Common Issues

1. **"Error fetching access token: redirect_uri_mismatch"**
   - Make sure you've added all the redirect URIs to your OAuth client in Google Cloud Console
   - Make sure the redirect URI in the code matches one of the authorized URIs
   
2. **"Token not found" or "Invalid token"**
   - Run the `app:google-api-token` command again to generate a new token

3. **"Access token expired and no refresh token available"**
   - You need to revoke access in your Google Account settings and re-authenticate
   - Go to [Google Account Permissions](https://myaccount.google.com/permissions)
   - Find your app and revoke access
   - Run the `app:google-api-token` command again

4. **"Google Calendar API access denied"**
   - Make sure the Calendar API is enabled in your Google Cloud Console
   - Check that you've granted the correct scopes during authentication

## Using in Production

For production usage:
1. Update the OAuth consent screen to "In Production" status
2. Add appropriate redirect URIs for your production environment
3. Update your environment variables or configuration files with the production paths

## More Information

- [Google Calendar API Documentation](https://developers.google.com/calendar)
- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2) 