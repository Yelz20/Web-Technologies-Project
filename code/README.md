# Football Review Platform ⚽

A comprehensive web application for football enthusiasts to track matches, leagues, and teams while engaging with other fans through reviews and reactions.

## 🚀 Features

### For Fans
- **Match Tracking**: View real-time (demo) match scores, events (goals, cards, subs), and detailed statistics.
- **Leagues & Teams**: Browse comprehensive lists of football leagues and teams with their historical data and stadiums.
- **Social Engagement**: Write reviews for matches, react to other fans' reviews (likes/dislikes), and participate in discussion threads.
- **Personalized Profile**: Manage your own profile with custom avatars and bios.
- **Support Center**: Direct messaging system to communicate with administrators.

### For Administrators
- **Content Management**: Full CRUD operations for matches, teams, and leagues.
- **Match Event Control**: Live management of match events and timelines.
- **User Moderation**: Monitor and manage user-generated content.
- **Support Dashboard**: Centralized hub to respond to fan inquiries.

## 🛠️ Technical Stack

- **Backend**: PHP 8.x
- **Database**: MySQL / MariaDB
- **Frontend**: Tailwind CSS, Vanilla JavaScript, FontAwesome
- **Architecture**: Modular PHP with a Singleton Database pattern.

## 📂 Directory Structure

```text
/FootballReview
├── /admin             # Administrative dashboard & management tools
├── /api               # Server-side API endpoints (react, reply, auth)
├── /assets            # Public assets (Custom CSS modules, Images, JS)
├── /includes          # Core application logic
│   ├── /partials      # Site-wide templates (Header, Footer)
│   ├── config.php     # System-wide settings & absolute path resolution
│   └── functions.php  # Global helper functions
├── /uploads           # User-uploaded content (avatars, logos)
└── *.php              # Main site pages (index, matches, match detail)
```

## ⚙️ Installation & Setup

1.  **Clone the project** into your local web server directory (e.g., `htdocs` for XAMPP).
2.  **Import the Database**:
    - Open phpMyAdmin.
    - Create a database named `webtech_2025A_yelsom_sanid`.
    - Import the `/database.sql` file.
3.  **Configure Database**:
    - Edit `includes/config.php`.
    - Update `DB_HOST`, `DB_USER`, and `DB_PASS` to match your local environment.
4.  **Access the Site**:
    - Navigate to `http://localhost/FootballReview` in your browser.

## 🔑 Base URL Logic
The project uses a robust absolute path resolution system. The `BASE_URL` is automatically calculated in `config.php`, ensuring that all assets and links work perfectly even when the project is deployed in subdirectories or `~user` environments.

## 📄 License
This project is part of a Web Technology course for 2024/2025.
