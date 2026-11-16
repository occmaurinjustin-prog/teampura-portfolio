# TEAMPURA PORTFOLIO — BSIT 4-Member Portfolio (Opol Community College)

A modern, responsive, drag-and-drop **WordPress + Elementor** portfolio for **Team Teampura** from **Opol Community College (BSIT)**, showcasing our collective projects—highlighted by our live logistics platform [**Dennis Trucking Services (dts-logistics.tech)**](https://dts-logistics.tech)—and dedicated individual portfolios for each member.

---

## Team Members & Roles

| Member | Role | Dedicated Page |
| :--- | :--- | :--- |
| **Justin Maurin** | Lead Programmer & Software Developer | `/justin-maurin/` |
| **Mike Iris Ebojo** | Project Manager & Team Coordinator | `/mike-iris-ebojo/` |
| **Christian Angelo Enerio** | System Analyst & Process Designer | `/christian-angelo-enerio/` |
| **Rhea Mae Marandang** | Technical Writer & Documentation Specialist | `/rhea-mae-marandang/` |

---

## Key Features & Architecture
- **100% Drag-and-Drop Editable**: Built on **Astra Theme** + **Elementor** containers and native Image, Heading, Text Editor, and Button widgets.
- **Custom Styles & Animations**: Enqueued cleanly via [`wp-content/mu-plugins/teampura-styles.php`](wp-content/mu-plugins/teampura-styles.php) and [`wp-content/themes/astra/assets/css/teampura-industry.css`](wp-content/themes/astra/assets/css/teampura-industry.css).
- **Dynamic Personal Navigation**: Automatically switches the floating pill header into a personal menu (`Home | Services | About | Skills | Projects | Switch Member | Contact Me`) when viewing an individual member's portfolio.
- **Featured Production System**: Showcases [**Dennis Trucking Services (`https://dts-logistics.tech`)**](https://dts-logistics.tech) with role-specific deliverables on every member portfolio.
- **Included Database Export**: Full WordPress + Elementor database snapshot saved at [`database/professional-portfolio.sql`](database/professional-portfolio.sql).

---

## Local Setup Instructions (Laragon / XAMPP)

1. **Clone the repository** into your web root (e.g., `C:\laragon\www\PROFESSIONAL-PORTFOLIO`):
   ```bash
   git clone https://github.com/occmaurinjustin-prog/teampura-portfolio.git PROFESSIONAL-PORTFOLIO
   ```
2. **Create the MySQL database** named `PROFESSIONAL-PORTFOLIO` (via phpMyAdmin or HeidiSQL).
3. **Import the database** from [`database/professional-portfolio.sql`](database/professional-portfolio.sql) into `PROFESSIONAL-PORTFOLIO`.
4. **Configure `wp-config.php`**:
   - Copy `wp-config-sample.php` to `wp-config.php`.
   - Set `DB_NAME` to `'PROFESSIONAL-PORTFOLIO'`, `DB_USER` to `'root'`, `DB_PASSWORD` to `''`, and `DB_HOST` to `'localhost'`.
5. **Open in Browser**:
   - Visit `http://professional-portfolio.test/` (Laragon auto-virtualhost) or `http://localhost/PROFESSIONAL-PORTFOLIO/`.
   - If migrating to a different local URL, go to **Elementor -> Tools -> Replace URL** or update `siteurl` and `home` in `wp_options`.
