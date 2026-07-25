# Sales & Customer DBMS — Oil & Gas
**Group Project | 2025BCS141 — Anthony**

## Project Structure
```
Sales_and_Customer_DBMS/
├── index.php          ← Dashboard (KPIs, charts, credit alerts)
├── customers.php      ← Customers CRUD
├── products.php       ← Products CRUD
├── sales.php          ← Sales Orders CRUD
├── db.php             ← Database connection
├── sidebar.php        ← Shared sidebar navigation
├── assets/
│   └── style.css      ← Dark industrial theme
└── README.md
```

## Database
- **Name:** `oilgassalesdb`
- **Tables:** `Customers`, `Products`, `SalesOrders`

## XAMPP Setup
1. Copy this folder to `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux)
2. Start Apache + MySQL in XAMPP Control Panel
3. Open browser → `http://localhost/Sales_and_Customer_DBMS/`

## GitHub Push
```bash
cd Sales_and_Customer_DBMS
git init
git remote add origin https://github.com/2025bcs141-anthony/Sales_and_Cutomer_DBMS.git
git add .
git commit -m "Initial commit: PHP UI for Oil & Gas Sales DBMS"
git branch -M main
git push -u origin main

## Project Presentation Video
The 10-minute presentation video demonstrating the system functionality can be viewed here:
[Click here to watch the Presentation Video](https://drive.google.com/file/d/1S2DbMVazGDzSenzu6qlpLYuh0PaWdb4j/view?usp=sharing)



```
