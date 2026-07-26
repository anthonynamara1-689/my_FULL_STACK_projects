<?php

declare(strict_types=1);

class CustomerService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getCustomers(): array
    {
        $sql = "SELECT C.CustomerID, C.CustomerName, C.Address, C.CreditLimit,
                COUNT(O.OrderID) AS OrderCount,
                COALESCE(SUM(O.QuantityLiters * P.UnitPrice), 0) AS Total_Spent
                FROM Customers C
                LEFT JOIN SalesOrders O ON O.CustomerID = C.CustomerID
                LEFT JOIN Products P ON P.ProductID = O.ProductID
                GROUP BY C.CustomerID, C.CustomerName, C.Address, C.CreditLimit
                ORDER BY C.CustomerID ASC";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function getCustomer(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM Customers WHERE CustomerID = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function createCustomer(array $data): int
    {
        $name = trim((string) ($data['CustomerName'] ?? ''));
        $address = trim((string) ($data['Address'] ?? ''));
        $creditLimit = (float) ($data['CreditLimit'] ?? 0);

        if ($name === '') {
            throw new InvalidArgumentException('Customer name is required.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO Customers (CustomerName, Address, CreditLimit) VALUES (?, ?, ?)');
        $stmt->execute([$name, $address, $creditLimit]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateCustomer(int $id, array $data): void
    {
        $name = trim((string) ($data['CustomerName'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Customer name is required.');
        }

        $address = trim((string) ($data['Address'] ?? ''));
        $creditLimit = (float) ($data['CreditLimit'] ?? 0);

        $stmt = $this->pdo->prepare('UPDATE Customers SET CustomerName = ?, Address = ?, CreditLimit = ? WHERE CustomerID = ?');
        $stmt->execute([$name, $address, $creditLimit, $id]);
    }

    public function deleteCustomer(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM SalesOrders WHERE CustomerID = ?');
        $stmt->execute([$id]);
        if ((int) $stmt->fetchColumn() > 0) {
            throw new RuntimeException('Cannot delete: this customer has existing sales orders.');
        }

        $stmt = $this->pdo->prepare('DELETE FROM Customers WHERE CustomerID = ?');
        $stmt->execute([$id]);
    }

    public function hasSales(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM SalesOrders WHERE CustomerID = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
