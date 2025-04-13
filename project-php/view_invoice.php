<?php
// ກວດສອບວ່າມີ ID ໃບບິນສົ່ງມາຫຼືບໍ່
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $invoice_id = $_GET['id'];

    // ກຳນົດຂໍ້ມູນການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ (ໃຫ້ແນ່ໃຈວ່າມັນຖືກຕ້ອງ)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "billing_db";

    // ສ້າງການເຊື່ອມຕໍ່
    $conn = new mysqli($servername, $username, $password, $dbname);

    // ກວດສອບການເຊື່ອມຕໍ່
    if ($conn->connect_error) {
        die("ການເຊື່ອມຕໍ່ຖານຂໍ້ມູນລົ້ມເຫລວ: " . $conn->connect_error);
    }

    // ດຶງຂໍ້ມູນໃບບິນຫຼັກ
    $sql_invoice = "SELECT invoice_number, customer_name, invoice_date, subtotal, tax_amount, grand_total FROM invoices WHERE invoice_id = ?";
    $stmt_invoice = $conn->prepare($sql_invoice);
    $stmt_invoice->bind_param("i", $invoice_id);
    $stmt_invoice->execute();
    $result_invoice = $stmt_invoice->get_result();
    $invoice = $result_invoice->fetch_assoc();

    // ດຶງລາຍລະອຽດຂອງລາຍການໃນໃບບິນ
    $sql_items = "SELECT item_name, quantity, price_per_unit, item_total FROM invoice_items WHERE invoice_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $invoice_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $items = $result_items->fetch_all(MYSQLI_ASSOC);

    // ປິດ Statement
    $stmt_invoice->close();
    $stmt_items->close();

    // ປິດການເຊື່ອມຕໍ່
    $conn->close();
} else {
    echo "<p style='color: red; text-align: center;'>ບໍ່ພົບໃບບິນ.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ໃບບິນເລກທີ: <?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <style>
        body { font-family: sans-serif; }
        .invoice { width: 1200px; margin: 20px auto; border: 1px solid #ccc; padding: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .invoice-details { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-row td { text-align: right; font-weight: bold; }
    </style>
</head>
<body>

    <div class="invoice">
        <h2>ໃບບິນ</h2>
        <div class="invoice-header">
            <div>
            <strong>ລູກຄ້າ:</strong> <?php echo htmlspecialchars($invoice['customer_name']); ?>
               
            </div>
            <div>
            <strong>ເລກບິນ:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?><br>
            <strong>ວັນທີອອກບິນ:</strong> <?php echo htmlspecialchars($invoice['invoice_date']); ?>
            </div>
        </div>

        <h3>ລາຍການ</h3>
        <table>
            <thead>
                <tr><th>ລຳດັບ</th><th>ລາຍການ</th><th>ຈຳນວນ</th><th>ລາຄາຕໍ່ໜ່ວຍ</th><th>ລາຄາລວມ</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php $i = 1; ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($i); ?></td>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td style='text-align: right;'><?php echo htmlspecialchars(number_format($item['price_per_unit'], 2)); ?></td>
                            <td style='text-align: right;'><?php echo htmlspecialchars(number_format($item['item_total'], 2)); ?></td>
                        </tr>
                    <?php $i++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">ບໍ່ມີລາຍການໃນໃບບິນນີ້.</td></tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="4">ລວມຍ່ອຍ:</td>
                    <td style='text-align: right;'><?php echo htmlspecialchars(number_format($invoice['subtotal'], 2)); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4">ອາກອນ (10%):</td>
                    <td style='text-align: right;'><?php echo htmlspecialchars(number_format($invoice['tax_amount'], 2)); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4">ລາຄາທັງໝົດ:</td>
                    <td style='text-align: right;'><?php echo htmlspecialchars(number_format($invoice['grand_total'], 2)); ?></td>
                </tr>
            </tbody>
        </table>

        <p style='text-align: center;'><a href='invoice_list.php'>ກັບໄປລາຍການໃບບິນ</a></p>
    </div>

</body>
</html>