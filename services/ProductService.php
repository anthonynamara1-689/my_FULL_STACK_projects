<?php

declare(strict_types=1);

class ProductService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getProducts(): array
    {
        $sql = "SELECT P.ProductID, P.FuelType, P.UnitPrice,
                COUNT(O.OrderID) AS OrderCount,
                COALESCE(SUM(O.QuantityLiters), 0) AS TotalLiters,
                COALESCE(SUM(O.QuantityLiters * P.UnitPrice), 0) AS TotalRevenue
                FROM Products P
                LEFT JOIN SalesOrders O ON O.ProductID = P.ProductID
                GROUP BY P.ProductID, P.FuelType, P.UnitPrice
                ORDER BY P.ProductID ASC";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function createProduct(array $data): int
    {
        $fuel = trim((string) ($data['FuelType'] ?? ''));
        if ($fuel === '') {
            throw new InvalidArgumentException('Fuel type is required.');
        }

        $price = (float) ($data['UnitPrice'] ?? 0);
        $stmt = $this->pdo->prepare('INSERT INTO Products (FuelType, UnitPrice) VALUES (?, ?)');
        $stmt->execute([$fuel, $price]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateProduct(int $id, array $data): void
    {
        $fuel = trim((string) ($data['FuelType'] ?? ''));
        if ($fuel === '') {
            throw new InvalidArgumentException('Fuel type is required.');
        }

        $price = (float) ($data['UnitPrice'] ?? 0);
        $stmt = $this->pdo->prepare('UPDATE Products SET FuelType = ?, UnitPrice = ? WHERE ProductID = ?');
        $stmt->execute([$fuel, $price, $id]);
    }

    public function deleteProduct(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM SalesOrders WHERE ProductID = ?');
        $stmt->execute([$id]);
        if ((int) $stmt->fetchColumn() > 0) {
            throw new RuntimeException('Cannot delete: this product is referenced in existing sales orders.');
        }

        $stmt = $this->pdo->prepare('DELETE FROM Products WHERE ProductID = ?');
        $stmt->execute([$id]);
    }
}
