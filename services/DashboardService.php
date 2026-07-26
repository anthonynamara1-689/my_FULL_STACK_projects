<?php

declare(strict_types=1);

class DashboardService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getDashboardStats(): array
    {
        $totalCustomers = (int) $this->pdo->query('SELECT COUNT(*) FROM Customers')->fetchColumn();
        $totalProducts = (int) $this->pdo->query('SELECT COUNT(*) FROM Products')->fetchColumn();
        $totalOrders = (int) $this->pdo->query('SELECT COUNT(*) FROM SalesOrders')->fetchColumn();
        $totalRevenue = (float) $this->pdo->query(
            'SELECT COALESCE(SUM(O.QuantityLiters * P.UnitPrice), 0) FROM SalesOrders O JOIN Products P ON O.ProductID = P.ProductID'
        )->fetchColumn();
        $totalLiters = (float) $this->pdo->query('SELECT COALESCE(SUM(QuantityLiters), 0) FROM SalesOrders')->fetchColumn();

        return [
            'customers' => $totalCustomers,
            'products' => $totalProducts,
            'orders' => $totalOrders,
            'revenue' => $totalRevenue,
            'liters' => $totalLiters,
        ];
    }

    public function getRecentOrders(): array
    {
        $sql = "SELECT O.OrderID, O.OrderDate, ROUND(O.QuantityLiters, 0) AS QuantityLiters,
                C.CustomerName, P.FuelType,
                ROUND(O.QuantityLiters * P.UnitPrice, 2) AS TotalAmount
                FROM SalesOrders O
                JOIN Customers C ON O.CustomerID = C.CustomerID
                JOIN Products P ON O.ProductID = P.ProductID
                ORDER BY O.OrderDate DESC LIMIT 5";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getTopCustomers(): array
    {
        $sql = "SELECT C.CustomerName,
                ROUND(SUM(O.QuantityLiters * P.UnitPrice), 2) AS Total_Spent,
                COUNT(O.OrderID) AS Orders
                FROM SalesOrders O
                JOIN Customers C ON O.CustomerID = C.CustomerID
                JOIN Products P ON O.ProductID = P.ProductID
                GROUP BY C.CustomerID, C.CustomerName
                ORDER BY Total_Spent DESC LIMIT 5";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getFuelSales(): array
    {
        $sql = "SELECT P.FuelType,
                ROUND(SUM(O.QuantityLiters), 0) AS Total_Liters_Sold,
                COUNT(O.OrderID) AS Number_of_Orders
                FROM SalesOrders O JOIN Products P ON O.ProductID = P.ProductID
                GROUP BY P.FuelType ORDER BY Total_Liters_Sold DESC";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getCreditAlertCustomers(): array
    {
        $sql = "SELECT CustomerName, CreditLimit,
                (SELECT COALESCE(SUM(O.QuantityLiters * P.UnitPrice), 0)
                 FROM SalesOrders O
                 JOIN Products P ON O.ProductID = P.ProductID
                 WHERE O.CustomerID = Customers.CustomerID) AS Current_Debt
                FROM Customers
                WHERE (SELECT COALESCE(SUM(O.QuantityLiters * P.UnitPrice), 0)
                       FROM SalesOrders O
                       JOIN Products P ON O.ProductID = P.ProductID
                       WHERE O.CustomerID = Customers.CustomerID) > (0.8 * CreditLimit)";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getCreditStatus(): array
    {
        $sql = "SELECT CustomerName, CreditLimit,
                (SELECT COALESCE(SUM(O.QuantityLiters * P.UnitPrice), 0)
                 FROM SalesOrders O
                 JOIN Products P ON O.ProductID = P.ProductID
                 WHERE O.CustomerID = Customers.CustomerID) AS Current_Debt
                FROM Customers";

        return $this->pdo->query($sql)->fetchAll();
    }
}
