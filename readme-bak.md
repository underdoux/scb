# Social Content Builder (SCB) Project

## Overview
Social Content Builder (SCB) is a full-stack web application designed to help users generate, schedule, and manage social media posts across multiple platforms such as Facebook, Instagram, Twitter, and LinkedIn. The system integrates content generation, scheduling, OAuth-based platform authentication, and notification management to provide a seamless social media management experience.

## Project Structure
- **Backend (PHP)**
  - Located in `scb/src`, `scb/scripts`, and `scb/views`
  - Handles database interactions, OAuth token management, scheduling logic, and API endpoints
  - Key scripts:
    - `create_posts_schedules_tables.php`: Creates `posts` and `schedules` tables in the database
    - `create_notifications_table.php`: Creates `notifications` table
    - `schedule_runner.php`: Processes scheduled posts, sends them to platforms, handles token refresh, and logs notifications
  - Views provide UI pages for schedules, posts, notifications, dashboard, and content generation

- **Frontend (Vue.js)**
  - Located in `scb/frontend/src`
  - Uses Vue 3 with Vue Router and Tailwind CSS for UI
  - Components:
    - `ScheduleForm.vue`: Form to create new scheduled posts with options for custom or generated content, platform selection, and scheduling time
    - `NotificationCenter.vue`: Real-time notification bell with dropdown, showing notifications with read/unread status and color-coded types
    - Other components include Home, Dashboard, ConnectedAccounts, ContentGenerator, and more
  - Tailwind CSS configured with PostCSS and plugins for forms and typography
  - Development server runs on port 8000

## Database Schema
- **posts**
  - `id`: Primary key
  - `user_id`: User who created the post
  - `content`: Text content of the post
  - `created_at`: Timestamp of creation

- **schedules**
  - `id`: Primary key
  - `post_id`: Foreign key to `posts`
  - `scheduled_time`: When the post should be published
  - `status`: Status of the schedule (`pending`, `sent`, `failed`)
  - `platform`: Target social media platform

- **notifications**
  - `id`: Primary key
  - `user_id`: User receiving the notification
  - `message`: Notification message
  - `type`: Notification type (`success`, `failure`, `token_issue`)
  - `created_at`: Timestamp of notification

## Scheduling Flow
1. User creates a scheduled post via the frontend `ScheduleForm.vue`.
2. The post content and schedule details are saved in the backend database (`posts` and `schedules` tables).
3. The `schedule_runner.php` script runs periodically (e.g., via cron) to:
   - Fetch pending schedules due for posting
   - Send posts to respective platforms using OAuth tokens
   - Handle token expiration by refreshing tokens and retrying
   - Update schedule status (`sent` or `failed`)
   - Log notifications in the `notifications` table and send email alerts

## Notification System
- Backend logs notifications for scheduling events, token issues, and failures.
- Frontend displays notifications in real-time using `NotificationCenter.vue` with:
  - Unread count badge
  - Color-coded notification types
  - Mark as read/unread and clear all options
  - Polling every 30 seconds to fetch new notifications
- Notifications are also accessible via a dedicated notifications page routed in Vue Router.

## Development Setup
- Frontend dependencies managed via `package.json` with Vue 3, Tailwind CSS, PostCSS, and Vite.
- Backend uses PHP with SQLite or other supported databases.
- Run frontend dev server:
  ```bash
  cd scb/frontend
  npm install
  npm run dev -- --host --port 8000
  ```
- Backend scripts can be run manually or scheduled via cron jobs.

## Notes
- Tailwind CSS is configured with PostCSS and plugins for forms and typography.
- OAuth tokens are managed securely with refresh logic in place.
- Notifications include email alerts for critical events.
- The project uses modern Vue 3 features with composition API and router.

---

This README provides a comprehensive summary of the SCB project, its architecture, flow, and key components to maintain consistency and facilitate ongoing development.
