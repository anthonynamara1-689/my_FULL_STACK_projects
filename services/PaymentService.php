<?php

declare(strict_types=1);

class PaymentService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function recordPayment(array $data): int
    {
        $customerId = (int) ($data['CustomerID'] ?? 0);
        $amount = (float) ($data['Amount'] ?? 0);
        $paymentDate = trim((string) ($data['PaymentDate'] ?? date('Y-m-d')));

        if ($customerId <= 0 || $amount <= 0) {
            throw new InvalidArgumentException('Please provide a valid customer and payment amount.');
        }

        $stmt = $this->pdo->prepare('INSERT INTO Payments (CustomerID, Amount, PaymentDate) VALUES (?, ?, ?)');
        $stmt->execute([$customerId, $amount, $paymentDate]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getPayments(): array
    {
        $sql = 'SELECT * FROM Payments ORDER BY PaymentDate DESC';
        return $this->pdo->query($sql)->fetchAll();
    }
}
