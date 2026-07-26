<?php

declare(strict_types=1);

class PaymentService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensurePaymentsTable();
    }

    public function ensurePaymentsTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS Payments (
            PaymentID INTEGER PRIMARY KEY AUTOINCREMENT,
            CustomerID INTEGER NOT NULL,
            Amount REAL NOT NULL,
            PaymentDate TEXT NOT NULL DEFAULT (date('Y-m-d')),
            FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID)
        )");
    }

    public function recordPayment(array $data): int
    {
        $customerId = (int) ($data['CustomerID'] ?? 0);
        $amount = (float) ($data['Amount'] ?? 0);
        $paymentDate = trim((string) ($data['PaymentDate'] ?? date('Y-m-d')));

        if ($customerId <= 0 || $amount <= 0) {
            throw new InvalidArgumentException('Please provide a valid customer and payment amount.');
        }

        if ($paymentDate === '') {
            $paymentDate = date('Y-m-d');
        }

        $stmt = $this->pdo->prepare('INSERT INTO Payments (CustomerID, Amount, PaymentDate) VALUES (?, ?, ?)');
        $stmt->execute([$customerId, $amount, $paymentDate]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getPayments(): array
    {
        $sql = 'SELECT p.PaymentID, p.CustomerID, c.CustomerName, p.Amount, p.PaymentDate
                FROM Payments p
                LEFT JOIN Customers c ON c.CustomerID = p.CustomerID
                ORDER BY p.PaymentDate DESC, p.PaymentID DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getCustomers(): array
    {
        return $this->pdo->query('SELECT CustomerID, CustomerName FROM Customers ORDER BY CustomerName')->fetchAll();
    }
}
