# DragonStone Store

**DragonStone Store** is a robust, full-stack e-commerce platform built entirely with core **PHP and MySQL**, designed to highlight sustainable and eco-friendly shopping. It tracks "product carbon emissions" alongside standard e-commerce features, blending modern UI/UX with a meaningful theme.

This project was built from the ground up without heavy frameworks to demonstrate a deep understanding of core web development concepts, including backend logic, relational database design, session management, and frontend styling.

## Screenshots

<!-- Add your screenshots here. For example: -->
<!-- ![Homepage](Project%20Sreenshots/homepage.png) -->
*(Replace this section with your actual project screenshots)*

## Key Features

- **User Authentication & Profiles:** Secure signup, login, and account management, allowing users to track their order history and update personal information.
- **Dynamic Product Catalog:** Products are fetched dynamically from the MySQL database with category filtering. Each product page features standard pricing alongside an eco-conscious "Carbon Emissions" metric.
- **Shopping Cart & Checkout Flow:** A fully functional cart utilizing PHP session variables (`$_SESSION`), complete with quantity updates and a streamlined checkout process.
- **Order Management:** Users can view their past orders and track their purchases.
- **Subscriptions:** A module to handle recurring user subscriptions.
- **Community Hub:** An interactive forum/post area where authenticated users can share posts and engage with the community.
- **Responsive UI/UX:** A modern, mobile-friendly interface built with custom CSS (Montserrat font, fixed floating cart navigation, and a cohesive eco-themed color palette).

## Tech Stack

- **Backend:** PHP (Vanilla, utilizing prepared statements to prevent SQL injection)
- **Database:** MySQL (Relational DB managing users, products, orders, items, and community posts)
- **Frontend:** HTML5, CSS3, Vanilla JavaScript

## What This Project Demonstrates

- **Database Architecture:** Designing and interacting with a multi-table relational database (7 interconnected tables including users, products, orders, and community).
- **Security Best Practices:** Implementing secure authentication, input sanitization (`filter_input`), HTML entity decoding, and parameterized SQL queries to protect against XSS and SQL Injection.
- **State Management:** Effective use of PHP sessions to maintain cart state and user login status across the application.
- **Full-Stack Integration:** Seamlessly bridging the gap between database schema, backend PHP logic, and frontend presentation.

---
*For database setup instructions, please see [db/README.md](db/README.md).*
