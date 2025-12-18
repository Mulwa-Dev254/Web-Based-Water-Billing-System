<?php
require_once __DIR__ . '/../app/config/db.php';

$allowed = "('bill_payment','service_payment','penalty')";

$sql1 = "UPDATE payments p INNER JOIN bills b ON b.id = p.reference_id
         SET p.type = 'bill_payment'
         WHERE (p.type = '' OR p.type IS NULL OR p.type NOT IN $allowed)";
$conn->query($sql1);
echo "bill_payment updates: " . $conn->affected_rows . "\n";

$sql2 = "UPDATE payments p LEFT JOIN bills b ON b.id = p.reference_id
         SET p.type = 'service_payment'
         WHERE (p.type = '' OR p.type IS NULL OR p.type NOT IN $allowed) AND b.id IS NULL";
$conn->query($sql2);
echo "service_payment updates: " . $conn->affected_rows . "\n";

$sql3 = "UPDATE payments SET type = 'service_payment' WHERE type = 'plan_renewal'";
$conn->query($sql3);
echo "plan_renewal normalized: " . $conn->affected_rows . "\n";

echo "done\n";
