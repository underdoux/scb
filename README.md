# PHP Social Media Integration Application

## Summary of Work Done

This project implements a PHP-based web application with the following key features:

### 1. User Authentication and Role Management
- Secure user registration and login system.
- Role-based access control with regular users and admin roles.
- Admin dashboard with user management capabilities.

### 2. OAuth Integration for Social Media Platforms
- OAuth login and token management for Facebook Pages, Twitter (X), and LinkedIn.
- Secure storage of access tokens, refresh tokens, and expiration times in SQLite.
- Token refresh mechanism to maintain valid access.
- UI components for connecting and disconnecting social media accounts.
- Display of OAuth callback URLs with copy-to-clipboard functionality.

### 3. OpenAI GPT Integration
- API endpoint to generate social media content (captions, hashtags, variations) using OpenAI GPT-3.5-turbo.
- Secure API key management via admin settings.
- Token limit and temperature control for efficient content generation.

### 4. Admin API Settings Page
- Centralized admin interface to configure API keys and OAuth credentials.
- Secure storage and retrieval of API credentials in the database.
- Connection testing for OpenAI and OAuth platforms.
- Styled UI with clear feedback and usability enhancements.

### 5. Routing and Middleware
- Clean routing system with authentication and authorization middleware.
- API endpoints secured and integrated with frontend UI.

### 6. Styling and UI
- Responsive and modern UI using CSS with Font Awesome icons.
- Clear navigation and user feedback mechanisms.

## Coding Flow, Logic, and Syntax Guidelines

To maintain consistency and clarity throughout the codebase, the following conventions and patterns have been applied:

### Project Structure
- **src/**: Contains all PHP service classes handling business logic and API integrations.
- **views/**: Contains PHP view templates for rendering HTML pages.
- **routes/**: Contains routing logic and request handlers.
- **config/**: Contains configuration files for API keys and OAuth credentials.
- **public/**: Contains public assets like CSS and entry point scripts.

### Coding Style
- Use **PSR-12** coding standards for PHP syntax and formatting.
- Use **camelCase** for method and variable names.
- Use **PascalCase** for class names.
- Use strict typing where possible and declare return types.
- Use **try-catch** blocks for error handling and throw exceptions with meaningful messages.
- Use **prepared statements** for all database queries to prevent SQL injection.
- Use **session management** for authentication and role-based access control.
- Use **namespaces** if the project scales (not currently implemented but recommended).

### Routing and Middleware
- Centralized routing in `routes/routes.php` using a switch-case on URI paths.
- Authentication middleware functions (`requireAuth()`, `requireAdmin()`, `guestOnly()`) to protect routes.
- Separate handler functions for each route to keep routing clean and maintainable.

### OAuth Integration Logic
- OAuthService class handles OAuth flows, token exchange, storage, and refresh.
- Store tokens securely in SQLite with expiration timestamps.
- Validate OAuth state parameter to prevent CSRF attacks.
- Support PKCE for Twitter OAuth 2.0.
- Provide UI for users to connect/disconnect social accounts.
- Display callback URLs in admin settings for easy configuration.

### OpenAI Integration Logic
- OpenAIService class handles API requests to OpenAI's chat completions endpoint.
- Use JSON messages with system and user roles to guide content generation.
- Limit max tokens to 200 for efficient responses.
- Handle API errors gracefully and provide meaningful error messages.

### Admin API Settings Logic
- ConfigService class manages API credentials storage and retrieval in SQLite.
- Provide connection testing endpoints to verify API keys.
- Admin UI for managing OpenAI and OAuth credentials with secure password inputs.
- Copy-to-clipboard functionality for OAuth callback URLs.

### UI and Styling
- Use semantic HTML5 elements.
- Use Font Awesome icons for visual cues.
- Responsive CSS with clear sectioning and spacing.
- Consistent button styles and alert messages.
- Use JavaScript for UI enhancements like copy-to-clipboard.

## Next Steps to Continue Development

1. **Enhance OAuth Support**
   - Add support for Instagram OAuth if needed.
   - Implement more granular permission scopes and error handling.
   - Add UI to display connected social media data (e.g., posts, analytics).

2. **Expand OpenAI Integration**
   - Add more content generation templates and customization options.
   - Implement caching and rate limiting for API calls.
   - Provide UI for users to request content generation.

3. **Improve Security**
   - Implement CSRF protection on forms.
   - Encrypt sensitive data in the database.
   - Add logging and monitoring for authentication and API usage.

4. **User Experience Enhancements**
   - Add user profile management.
   - Implement notifications for OAuth token expiration or errors.
   - Improve responsive design and accessibility.

5. **Testing and Deployment**
   - Write unit and integration tests.
   - Prepare deployment scripts and environment configuration.
   - Set up continuous integration and delivery pipelines.

6. **Documentation**
   - Expand documentation for developers and users.
   - Provide API documentation for integration.

This README provides a comprehensive guide to the current state of the project, coding standards, and future development plans to ensure consistent and maintainable code.
