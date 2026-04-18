## ️Database Setup (MySQL)

To run this project locally or on a hosting provider, you must first set up the database.



### 1. Create a Database

- Open **phpMyAdmin** (or your hosting control panel)
- Create a new database, for example:

```

dragonstone_db

```

---

### 2. Import the Database File

- Select the newly created database
- Click the **Import** tab
- Upload the file:

```

db/dragonstone_db.sql

```

- Click **Go**

This will automatically create all required tables:
- users  
- admins  
- products  
- orders  
- order_items  
- subscriptions  
- community_posts  

---

### 3. Fix Trigger Issue (Important ⚠️)

Some hosting providers (e.g. InfinityFree) **do not allow SQL triggers**.

If you see an error like:

```

#1142 - TRIGGER command denied

```

Solution:
- Open the `.sql` file in a text editor
- Find and remove any `CREATE TRIGGER` statements
- Save the file
- Re-import the database

The project will still work without triggers.

---

### 4. Configure Database Connection

Open the file:

```

db_connect.php

````

Update it with your database credentials:

```php
$host = "localhost";
$username = "your_db_username";
$password = "your_db_password";
$database = "dragonstone_db";

$conn = new mysqli($host, $username, $password, $database);
````

For InfinityFree, it may look like:

```php
$host = "sqlXXX.infinityfree.com";
$username = "if0_xxxxxxxx";
$password = "your_password";
$database = "if0_xxxxxxxx_dragonstone_db";
```

---

### 5. Test the Website

Run the project in your browser:

**Localhost:**

```
http://localhost/your-project-folder
```

**Live site:**

```
https://yourdomain.com
```

If everything is working:

* No database errors will appear
* Products and users will load correctly

---

### 6. Default Data (Optional)

The database includes sample data:

* Users
* Products
* Orders

You can log in with existing data or create a new account.

---

## Troubleshooting

**500 Internal Server Error**

* Check `db_connect.php` credentials
* Ensure the database exists

**Database import fails**

* Remove triggers from the `.sql` file
* Make sure the correct database is selected

**Images not displaying**

* Ensure the `/assets/` folder is uploaded correctly
* Check image paths in the database


