<?php

declare(strict_types=1);

class SalesService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSales(): array
    {
        $sql = "SELECT O.OrderID, O.OrderDate, O.QuantityLiters, O.CustomerID, O.ProductID,
                C.CustomerName, P.FuelType, P.UnitPrice,
                ROUND(O.QuantityLiters * P.UnitPrice, 2) AS TotalAmount
                FROM SalesOrders O
                JOIN Customers C ON O.CustomerID = C.CustomerID
                JOIN Products P ON O.ProductID = P.ProductID
                ORDER BY O.OrderDate DESC";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function createSale(array $data): int
    {
        $customerId = (int) ($data['CustomerID'] ?? 0);
        $productId = (int) ($data['ProductID'] ?? 0);
        $quantity = (float) ($data['QuantityLiters'] ?? 0);
        $orderDate = trim((string) ($data['OrderDate'] ?? date('Y-m-d')));

        if ($customerId <= 0 || $productId <= 0 || $quantity <= 0) {
            throw new InvalidArgumentException('Please provide a valid customer, product, and quantity.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO SalesOrders (CustomerID, ProductID, QuantityLiters, OrderDate) VALUES (?, ?, ?, ?)');
        $stmt->execute([$customerId, $productId, $quantity, $orderDate]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateSale(int $id, array $data): void
    {
        $customerId = (int) ($data['CustomerID'] ?? 0);
        $productId = (int) ($data['ProductID'] ?? 0);
        $quantity = (float) ($data['QuantityLiters'] ?? 0);
        $orderDate = trim((string) ($data['OrderDate'] ?? date('Y-m-d')));

        if ($customerId <= 0 || $productId <= 0 || $quantity <= 0) {
            throw new InvalidArgumentException('Please provide a valid customer, product, and quantity.');
        }

        $stmt = $this->pdo->prepare('UPDATE SalesOrders SET CustomerID = ?, ProductID = ?, QuantityLiters = ?, OrderDate = ? WHERE OrderID = ?');
        $stmt->execute([$customerId, $productId, $quantity, $orderDate, $id]);
    }

    public function deleteSale(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM SalesOrders WHERE OrderID = ?');
        $stmt->execute([$id]);
    }
}
