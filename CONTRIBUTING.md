# Contributing Guidelines for [MPS Monitor Dashboard]

This document outlines the conventions, best practices, and guidelines that **MUST** be followed when contributing to the `[MPS Monitor Dashboard]` codebase. Adhering to these guidelines is crucial for maintaining code quality, ensuring consistency, facilitating collaboration, and preventing issues like unmanaged files, incorrect naming, or lingering UI elements.

## Table of Contents

1.  [Running Log / Change Log](#1-running-log--change-log)
2.  [File and Folder Structure](#2-file-and-folder-structure)
3.  [Naming Conventions](#3-naming-conventions)
4.  [Commenting](#4-commenting)
5.  [General Coding Style & Best Practices](#5-general-coding-style--best-practices)

---

## 1. Running Log / Change Log

All changes, from the most minor fix to major new features, must be meticulously documented. This ensures traceability, aids in debugging, and provides a clear history for all contributors.

### 1.1. `CHANGELOG.md` File

* **Purpose:** To provide a high-level, human-readable summary of all significant changes, additions, fixes, and removals for each version or release. This is your project's public history.
* **Location:** Create and maintain a file named `CHANGELOG.md` in the project's root directory.
* **Format:** Adhere to the "Keep a Changelog" format (see [keepachangelog.com](https://keepachagelog.com/en/1.0.0/)). It is Markdown-based and follows Semantic Versioning.

    ```markdown
    # Changelog

    All notable changes to this project will be documented in this file.

    The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
    and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

    ## [Unreleased]
    ### Added
    - New feature or functionality.
    ### Changed
    - Modifications to existing features.
    ### Fixed
    - Bug fixes.
    ### Removed
    - Features or files that have been taken out.
    ### Security
    - Security vulnerability fixes or improvements.

    ## [0.1.2] - 2025-06-12
    ### Changed
    - Updated footer to include dynamic APP_NAME and APP_VERSION.
    ### Fixed
    - Corrected duplicate navigation issue by consolidating nav logic into `includes/navigation.php`.
    - Eliminated empty `<main></main>` tag and ensured single main content area.
    - Added missing `</div>` for `#wrapper` in `includes/footer.php`.
    - Removed "Select Customer" label and "Apply Filter" button from `includes/header.php`.
    - Removed "Logout" link from `includes/navigation.php`.
    - Restored "Dashboard Overview" and "Reports & Analytics" links in navigation.

    ## [0.1.1] - 2025-06-11
    ### Added
    - Initial project structure with `index.php`, `config.php`, `functions.php`, `header.php`, `footer.php`, `navigation.php`, `views/dashboard.php`, `views/reports.php`.
    - Basic routing and view rendering logic.
    - Database and API status indicators.
    - Theme toggle functionality.
    - Customer selection dropdown and search input.
    - Debug logging functionality.
    ### Fixed
    - Resolved `APP_NAME` undefined constant error by ensuring `config.php` loads .env.
    - Replaced `sanitize_url` with `urlencode`.
    - Corrected stylesheet link from `style.css` to `styles.css`.
    - Ensured `script.js` is correctly linked.
    ```
* **Guidelines for Entries:**
    * Each entry must describe a single, atomic change.
    * Be concise but informative.
    * Use imperative mood ("Add X", "Fix Y").
    * Reference related Git commit hashes or issue numbers if applicable.

### 1.2. Git Commit Messages

* **Purpose:** To provide a highly granular, chronological log of *every specific modification* to the codebase. This is essential for code review, using `git blame`, and understanding detailed changes.
* **Format:** Adhere to the **Conventional Commits** specification. This provides a lightweight convention on commit messages.

    ```
    <type>[(scope)]: <description>

    [optional body]

    [optional footer(s)]
    ```

    * **`<type>` (Mandatory):** A keyword indicating the *type* of change:
        * `feat`: A new feature.
        * `fix`: A bug fix.
        * `build`: Changes that affect the build system or external dependencies (e.g., Composer).
        * `chore`: Routine tasks, maintenance, or minor refactors without behavioral change (e.g., updating docs, refactoring variable names).
        * `ci`: Changes to CI configuration files and scripts.
        * `docs`: Documentation-only changes.
        * `perf`: A code change that improves performance.
        * `refactor`: A code change that neither fixes a bug nor adds a feature, but improves code structure.
        * `revert`: Reverts a previous commit.
        * `style`: Changes that do not affect the meaning of the code (whitespace, formatting, semicolons).
        * `test`: Adding missing tests or correcting existing tests.
    * **`(scope)` (Optional):** A noun describing the section of the codebase affected by the commit (e.g., `navigation`, `header`, `auth`, `database`). Use `*` if the change is global or affects multiple scopes.
    * **`<description>` (Mandatory):** A concise, imperative, present-tense description of the change (e.g., "add new feature," "fix bug," NOT "added new feature," "fixed bug"). Keep it short (max 72 characters).
    * **`[optional body]`:** More detailed explanatory text, if necessary. Each line should be wrapped at 72 characters.
    * **`[optional footer(s)]`:** Reference to issues (e.g., `Fixes #123`), breaking changes, or other meta-information.

    **Examples of Good Commit Messages:**

    ```
    feat(dashboard): add printer status overview card

    Adds a new dashboard card to display real-time printer status
    summary including online, offline, warning, and error counts.
    Resolves #456
    ```

    ```
    fix(navigation): remove duplicate nav and consolidate menu items

    Corrects the issue where two navigation menus were displayed on the page.
    Consolidates primary navigation logic into `includes/navigation.php`,
    ensuring only one menu is rendered. Also removes the `Logout` option
    from the menu as it's not currently used for this project.
    ```

---

## 2. File and Folder Structure

A well-defined file and folder structure ensures that all project assets are organized logically, making it easy for any contributor to find what they're looking for and to understand where new files should go. **Adherence to this structure is mandatory.**
 Coding partner

My apologies again for the fragmented delivery! You are absolutely right; a single, comprehensive Markdown document is exactly what's needed. I will provide all the coding conventions, logging procedures, and best practices in one complete CONTRIBUTING.md file.

This document will serve as your "dictionary" for how to code in this project, ensuring consistency, maintainability, and clarity for all contributors.
CONTRIBUTING.md
Markdown

# Contributing Guidelines for [Your Application Name]

This document outlines the conventions, best practices, and guidelines that **MUST** be followed when contributing to the `[Your Application Name]` codebase. Adhering to these guidelines is crucial for maintaining code quality, ensuring consistency, facilitating collaboration, and preventing issues like unmanaged files, incorrect naming, or lingering UI elements.

## Table of Contents

1.  [Running Log / Change Log](#1-running-log--change-log)
2.  [File and Folder Structure](#2-file-and-folder-structure)
3.  [Naming Conventions](#3-naming-conventions)
4.  [Commenting](#4-commenting)
5.  [General Coding Style & Best Practices](#5-general-coding-style--best-practices)

---

## 1. Running Log / Change Log

All changes, from the most minor fix to major new features, must be meticulously documented. This ensures traceability, aids in debugging, and provides a clear history for all contributors.

### 1.1. `CHANGELOG.md` File

* **Purpose:** To provide a high-level, human-readable summary of all significant changes, additions, fixes, and removals for each version or release. This is your project's public history.
* **Location:** Create and maintain a file named `CHANGELOG.md` in the project's root directory.
* **Format:** Adhere to the "Keep a Changelog" format (see [keepachangelog.com](https://keepachagelog.com/en/1.0.0/)). It is Markdown-based and follows Semantic Versioning.

    ```markdown
    # Changelog

    All notable changes to this project will be documented in this file.

    The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
    and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

    ## [Unreleased]
    ### Added
    - New feature or functionality.
    ### Changed
    - Modifications to existing features.
    ### Fixed
    - Bug fixes.
    ### Removed
    - Features or files that have been taken out.
    ### Security
    - Security vulnerability fixes or improvements.

    ## [0.1.2] - 2025-06-12
    ### Changed
    - Updated footer to include dynamic APP_NAME and APP_VERSION.
    ### Fixed
    - Corrected duplicate navigation issue by consolidating nav logic into `includes/navigation.php`.
    - Eliminated empty `<main></main>` tag and ensured single main content area.
    - Added missing `</div>` for `#wrapper` in `includes/footer.php`.
    - Removed "Select Customer" label and "Apply Filter" button from `includes/header.php`.
    - Removed "Logout" link from `includes/navigation.php`.
    - Restored "Dashboard Overview" and "Reports & Analytics" links in navigation.

    ## [0.1.1] - 2025-06-11
    ### Added
    - Initial project structure with `index.php`, `config.php`, `functions.php`, `header.php`, `footer.php`, `navigation.php`, `views/dashboard.php`, `views/reports.php`.
    - Basic routing and view rendering logic.
    - Database and API status indicators.
    - Theme toggle functionality.
    - Customer selection dropdown and search input.
    - Debug logging functionality.
    ### Fixed
    - Resolved `APP_NAME` undefined constant error by ensuring `config.php` loads .env.
    - Replaced `sanitize_url` with `urlencode`.
    - Corrected stylesheet link from `style.css` to `styles.css`.
    - Ensured `script.js` is correctly linked.
    ```
* **Guidelines for Entries:**
    * Each entry must describe a single, atomic change.
    * Be concise but informative.
    * Use imperative mood ("Add X", "Fix Y").
    * Reference related Git commit hashes or issue numbers if applicable.

### 1.2. Git Commit Messages

* **Purpose:** To provide a highly granular, chronological log of *every specific modification* to the codebase. This is essential for code review, using `git blame`, and understanding detailed changes.
* **Format:** Adhere to the **Conventional Commits** specification. This provides a lightweight convention on commit messages.

    ```
    <type>[(scope)]: <description>

    [optional body]

    [optional footer(s)]
    ```

    * **`<type>` (Mandatory):** A keyword indicating the *type* of change:
        * `feat`: A new feature.
        * `fix`: A bug fix.
        * `build`: Changes that affect the build system or external dependencies (e.g., Composer).
        * `chore`: Routine tasks, maintenance, or minor refactors without behavioral change (e.g., updating docs, refactoring variable names).
        * `ci`: Changes to CI configuration files and scripts.
        * `docs`: Documentation-only changes.
        * `perf`: A code change that improves performance.
        * `refactor`: A code change that neither fixes a bug nor adds a feature, but improves code structure.
        * `revert`: Reverts a previous commit.
        * `style`: Changes that do not affect the meaning of the code (whitespace, formatting, semicolons).
        * `test`: Adding missing tests or correcting existing tests.
    * **`(scope)` (Optional):** A noun describing the section of the codebase affected by the commit (e.g., `navigation`, `header`, `auth`, `database`). Use `*` if the change is global or affects multiple scopes.
    * **`<description>` (Mandatory):** A concise, imperative, present-tense description of the change (e.g., "add new feature," "fix bug," NOT "added new feature," "fixed bug"). Keep it short (max 72 characters).
    * **`[optional body]`:** More detailed explanatory text, if necessary. Each line should be wrapped at 72 characters.
    * **`[optional footer(s)]`:** Reference to issues (e.g., `Fixes #123`), breaking changes, or other meta-information.

    **Examples of Good Commit Messages:**

    ```
    feat(dashboard): add printer status overview card

    Adds a new dashboard card to display real-time printer status
    summary including online, offline, warning, and error counts.
    Resolves #456
    ```

    ```
    fix(navigation): remove duplicate nav and consolidate menu items

    Corrects the issue where two navigation menus were displayed on the page.
    Consolidates primary navigation logic into `includes/navigation.php`,
    ensuring only one menu is rendered. Also removes the `Logout` option
    from the menu as it's not currently used for this project.
    ```

---

## 2. File and Folder Structure

A well-defined file and folder structure ensures that all project assets are organized logically, making it easy for any contributor to find what they're looking for and to understand where new files should go. **Adherence to this structure is mandatory.**

├── .env                  # Environment-specific variables (e.g., database credentials, API keys)
├── CHANGELOG.md          # Detailed log of all notable changes (human-readable)
├── CONTRIBUTING.md       # (This document) Guidelines for contributors
├── README.md             # Project overview, setup instructions, etc.
├── index.php             # The single entry point for the application (router)
│
├── includes/             # Core application logic, configuration, common functions, and UI components
│   ├── config.php        # Loads .env variables, defines global constants (e.g., APP_NAME, BASE_URL)
│   ├── constants.php     # Other general PHP constants (if any, separate from config.php)
│   ├── functions.php     # Global helper functions (e.g., sanitize_html, render_view, debug_log)
│   │
│   ├── header.php        # HTML for the top section of every page (doctype, head, opening body/wrapper, app header)
│   ├── navigation.php    # HTML for the main application navigation menu
│   ├── footer.php        # HTML for the bottom section of every page (closing body/wrapper, actual footer content)
│   │
│   └── (optional)        # Subdirectories for specific modules or libraries if the project grows
│       ├── db/           # Database connection and interaction logic (e.g., db_connect.php, query_builder.php)
│       ├── auth/         # Authentication related functions/classes (e.g., auth_helpers.php)
│       └── third-party/  # Small, self-contained third-party PHP libraries (if not using Composer for them)
│
├── views/                # HTML templates for different pages/sections (rendered by render_view())
│   ├── dashboard.php     # Content for the dashboard view
│   ├── reports.php       # Content for the reports view
│   └── (optional)        # Subdirectories for nested or complex views (e.g., views/user-management/profile.php)
│
├── public/               # Publicly accessible static assets (CSS, JS, images, fonts)
│   ├── css/              # Stylesheets
│   │   └── styles.css    # Main application stylesheet
│   ├── js/               # JavaScript files
│   │   └── script.js     # Main application JavaScript
│   ├── img/              # Images
│   └── fonts/            # Web fonts
│
├── logs/                 # Directory for application log files (e.g., debug.log)
│   └── debug.log         # Main debug log file (managed by debug_log function)
│
└── (optional)
├── vendor/           # Composer dependencies (if Composer is introduced)
├── tests/            # Unit and integration tests (if testing framework is introduced)
└── .git/             # Git version control metadata (managed by Git)
#### **Guidelines for Structure:**

* **Logical Grouping:** Files **must** be grouped by their logical function (e.g., all HTML templates in `views`, all core PHP functions in `includes`).
* **Single Responsibility:** Avoid placing unrelated code in a single file (e.g., `header.php` should *only* contain header HTML and its associated PHP, not navigation or main content).
* **Flat vs. Nested:** For smaller projects, a relatively flat structure is preferred. As complexity grows, use subdirectories within `includes/` and `views/` to organize related files (e.g., `includes/db/` for database-related files).
* **`public/` Separation:** All static assets that the browser directly requests (CSS, JS, images) **must** reside within the `public/` directory to improve security and organization.
* **No Unnecessary Files:** Do not create empty directories or files that serve no purpose. If a directory is optional and not currently used, it **must not** exist in the repository.

---

## 3. Naming Conventions

Consistent naming is fundamental for code readability and maintainability. It helps contributors quickly understand the purpose and type of an element. **All naming conventions outlined below are mandatory.**

### 3.1. Files and Folders

* **Rule:** Use `kebab-case` (all lowercase, words separated by hyphens).
* **Reasoning:** Universally readable across different operating systems and web servers; avoids issues with case sensitivity on different environments.
* **Examples:**
    * `my-new-script.php`
    * `user-profile-view.php`
    * `includes/database-helpers/`
    * `css/main-styles.css`
    * `js/dashboard-charts.js`

### 3.2. PHP Naming Conventions

* **Variables:**
    * **Rule:** `camelCase` (starts with lowercase, subsequent words capitalized).
    * **Reasoning:** Standard PHP convention for variables (PSR-1/PSR-12).
    * **Examples:** `$userName`, `$currentViewSlug`, `$customerData`, `$dbStatus`
* **Functions (Global/Helper Functions):**
    * **Rule:** `snake_case` (all lowercase, words separated by underscores).
    * **Reasoning:** Consistent with existing helper functions like `sanitize_html()`, `render_view()`, `debug_log()`.
    * **Examples:** `get_user_by_id()`, `validate_input_string()`, `fetch_api_data()`
* **Classes:**
    * **Rule:** `PascalCase` (first letter of each word capitalized).
    * **Reasoning:** PSR-1 standard for class names.
    * **Examples:** `UserManager`, `DatabaseConnection`, `ApiHandler`
* **Class Methods:**
    * **Rule:** `camelCase`.
    * **Reasoning:** PSR-1 standard for method names.
    * **Examples:** `getUserDetails()`, `saveRecord()`, `establishConnection()`
* **Constants:**
    * **Rule:** `UPPER_SNAKE_CASE` (all uppercase, words separated by underscores).
    * **Reasoning:** Clear distinction from variables; common convention for constants.
    * **Examples:** `APP_NAME`, `BASE_URL`, `DB_HOST`, `MAX_FILE_SIZE`
* **Namespaces (if introduced):**
    * **Rule:** `PascalCase`, separated by backslashes (`\`).
    * **Reasoning:** PSR-4 standard for autoloading.
    * **Example:** `App\Core\Database`, `App\Services\Api`

### 3.3. HTML/CSS Naming Conventions

* **IDs and Classes:**
    * **Rule:** `kebab-case` (all lowercase, words separated by hyphens).
    * **Reasoning:** Standard convention in front-end development, aligns with file naming. Promotes readability and maintainability.
    * **Examples:** `<div id="main-content">`, `<button class="cta-button">`, `<section class="user-dashboard-section">`
* **CSS Properties and Values:**
    * **Rule:** Standard CSS properties and values (e.g., `background-color`, `font-size`).
    * **Reasoning:** Adherence to W3C standards.

### 3.4. JavaScript Naming Conventions

* **Variables:** `camelCase`
* **Functions:** `camelCase`
* **Constants:** `UPPER_SNAKE_CASE`
* **Classes (if introduced):** `PascalCase`
* **Reasoning:** Common JavaScript community conventions.

---

## 4. Commenting

Comments are vital for explaining complex logic, design choices, and assumptions. They explain *why* the code exists, not just *what* it does (which should be evident from clear code).

### 4.1. PHP Commenting

* **PHPDoc:** **Mandatory** for all PHP files, classes, functions, and methods.
    * **File-level Doc Block:** At the top of each PHP file, include a brief description of the file's purpose, followed by `@author` and `@since` (or date).
        ```php
        <?php
        /**
         * includes/functions.php
         *
         * Contains global helper functions for the application.
         * @author Your Name
         * @since 0.1.0
         */
        ```
    * **Class-level Doc Block:** For every class, include a brief description and any relevant `@property` tags for class properties.
        ```php
        /**
         * Manages user authentication and session data.
         * @property string $loggedInUserId The ID of the currently logged-in user.
         */
        class AuthManager { /* ... */ }
        ```
    * **Function/Method-level Doc Block:** For every function and method, describe its purpose, parameters (`@param`), return values (`@return`), and any exceptions it might throw (`@throws`).
        ```php
        /**
         * Sanitizes HTML strings to prevent XSS attacks.
         *
         * @param string $input The raw HTML string to sanitize.
         * @return string The sanitized HTML string.
         */
        function sanitize_html(string $input): string { /* ... */ }
        ```
* **Inline Comments:** Use `//` for single lines or short explanations of tricky logic within a function or block. Avoid excessive inline comments; well-written, self-documenting code is preferred.
    ```php
    // Ensure the customer ID is not null before proceeding
    if ($customer_id === null) { /* ... */ }
    ```
* **Block Comments:** Use `/* ... */` for larger blocks of descriptive text or for temporarily commenting out sections of code.
    ```php
    /*
     * This section handles the processing of financial data.
     * It performs multiple calculations and external API calls,
     * so be careful with modifications.
     */
    ```

### 4.2. HTML/CSS/JavaScript Comments

* **HTML:** Use `` for notes, structure explanations, or temporary disabling of content.
* **CSS:** Use `/* Your comment here */` for explaining complex styles, sections, or temporary disabling.
* **JavaScript:** Use `//` for single-line comments and `/* ... */` for multi-line comments. PHPDoc-like JSDoc comments are encouraged for functions/classes.

---

## 5. General Coding Style & Best Practices

Beyond naming and structure, consistent coding style and adherence to best practices prevent common pitfalls and make the codebase reliable and pleasant to work with.

### 5.1. Indentation & Whitespace

* **Rule:** Use 4 spaces for indentation. **DO NOT USE TABS.**
* **Whitespace:** Use consistent spacing around operators, after commas, and between code blocks to enhance readability.

### 5.2. Brace Style

* **Rule:** Use K&R style (opening brace on the same line as the control structure).
    ```php
    if (condition) {
        // ...
    } else {
        // ...
    }
    ```

### 5.3. Line Length

* **Rule:** Keep lines of code to a maximum of 120 characters. Break longer lines into multiple lines if necessary.
* **Reasoning:** Improves readability, especially on smaller screens or when reviewing code side-by-side.

### 5.4. PHP Specifics

* **Error Handling:**
    * **Rule:** Use `try-catch` blocks for handling expected exceptions (e.g., database connection failures, API request errors).
    * **Rule:** Do **NOT** use `die()` or `exit()` directly within core application logic or helper functions. For critical, unrecoverable errors, log them and display a generic error message to the user.
    * **Rule:** Utilize the `debug_log()` function for logging information, warnings, and errors.
* **Security:**
    * **Rule:** **ALWAYS** sanitize user input (from `$_GET`, `$_POST`, `$_SERVER`, etc.) before using it, especially when displaying it (`sanitize_html()`) or using it in database queries.
    * **Rule:** Use parameterized queries for all database interactions to prevent SQL injection (if a database is introduced).
    * **Rule:** Avoid direct file system access or redirects based on unsanitized user input.
* **Superglobals:** Access superglobals (`$_GET`, `$_POST`, `$_SESSION`, etc.) through helper functions (if available) or by careful direct use *after* validation and sanitization.
* **Configuration:** All sensitive data (API keys, database credentials, specific paths) **must** be stored in the `.env` file and accessed via `includes/config.php` (which loads `.env` variables). **Never hardcode sensitive information.**
* **Dependencies:** If external PHP libraries are needed, they **must** be managed using Composer. The `vendor/` directory should be excluded from version control.
* **Single Responsibility Principle:** Functions, classes, and even files should do one thing and do it well. Avoid "God objects" or "mega functions" that handle too many responsibilities.
* **Loose Coupling, High Cohesion:** Design components to be independent and self-contained, but work well together.

### 5.5. HTML Structure & Semantics

* **Rule:** Use semantic HTML5 elements (`<header>`, `<nav>`, `<main>`, `<footer>`, `<section>`, `<article>`, etc.) where appropriate.
* **Reasoning:** Improves accessibility, SEO, and maintainability.
* **Rule:** Ensure proper nesting and closing of all HTML tags.
* **Rule:** Avoid inline styles (`<div style="...">`). Use external CSS files.

### 5.6. CSS Guidelines

* **Rule:** Prefer a clear, consistent methodology for class naming, such as BEM (Block-Element-Modifier) or a similar system (e.g., `block-name__element-name--modifier-name`).
* **Rule:** Organize CSS logically (e.g., by component, by page section, or by media queries).
* **Rule:** Avoid `!important` unless absolutely necessary and with clear justification.
* **Rule:** Use external CSS files (e.g., `public/css/styles.css`).

### 5.7. JavaScript Guidelines

* **Rule:** Keep DOM manipulation to a minimum. Use efficient methods for selecting elements.
* **Rule:** Use `const` and `let` over `var` for variable declarations.
* **Rule:** Prefer modern ES6+ features (e.g., arrow functions, template literals).
* **Rule:** Organize JavaScript into logical modules or functions if complexity grows.
* **Rule:** Place `<script>` tags for application-specific JavaScript at the end of the `<body>` element (using `defer` attribute) to avoid blocking HTML rendering.

---

By following these conventions rigorously, we can ensure that MPS Monitor Dashboard remains a robust, maintainable, and easily understandable codebase for everyone involved.