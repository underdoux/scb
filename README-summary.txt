Social Content Builder (SCB) Project Summary

Overview:
SCB is a full-stack web app for generating, scheduling, and managing social media posts across platforms like Facebook, Instagram, Twitter, and LinkedIn. It integrates content generation, scheduling, OAuth authentication, and notifications.

Project Structure:
- Backend (PHP): Handles DB, OAuth, scheduling, API endpoints, and views.
- Frontend (Vue.js): Vue 3 with Vue Router and Tailwind CSS for UI components including scheduling forms and notification center.

Database Schema:
- posts: Stores user posts.
- schedules: Links posts to platforms with scheduled times and status.
- notifications: Stores user notifications with type and timestamp.

Scheduling Flow:
- Users create scheduled posts via frontend.
- Backend saves posts and schedules.
- schedule_runner.php processes pending schedules, posts content, refreshes tokens, updates status, and logs notifications.

Notification System:
- Backend logs notifications and sends email alerts.
- Frontend displays notifications with unread count, color-coded types, and read/unread management.
- Notifications page accessible via Vue Router.

Development Setup:
- Frontend uses npm with Vue 3, Tailwind CSS, PostCSS, and Vite.
- Backend uses PHP and SQLite or other DB.
- Run frontend dev server with npm run dev on port 8000.
- Backend scripts run manually or via cron.

Notes:
- Tailwind CSS configured with PostCSS plugins.
- OAuth tokens managed securely.
- Modern Vue 3 features used.

This summary file can be used to maintain consistency and facilitate ongoing development.
