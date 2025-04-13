<?php
// ກຳນົດຂໍ້ມູນການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
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

// ຮັບ ID ໃບບິນສຳລັບການແກ້ໄຂ
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $invoice_id = $_GET['id'];

    // ດຶງຂໍ້ມູນໃບບິນຫຼັກ
    $sql_invoice = "SELECT invoice_number, customer_name, invoice_date FROM invoices WHERE invoice_id = ?";
    $stmt_invoice = $conn->prepare($sql_invoice);
    $stmt_invoice->bind_param("i", $invoice_id);
    $stmt_invoice->execute();
    $result_invoice = $stmt_invoice->get_result();
    $invoice = $result_invoice->fetch_assoc();
    $stmt_invoice->close();

    // ດຶງລາຍລະອຽດຂອງລາຍການໃນໃບບິນ
    $sql_items = "SELECT item_id, item_name, quantity, price_per_unit FROM invoice_items WHERE invoice_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $invoice_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $items = $result_items->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    if (!$invoice) {
        echo "<p style='color: red; text-align: center;'>ບໍ່ພົບໃບບິນ.</p>";
        exit;
    }
} else {
    echo "<p style='color: red; text-align: center;'>ບໍ່ມີ ID ໃບບິນທີ່ຖືກສົ່ງມາ.</p>";
    exit;
}

// ປະມວນຜົນການບັນທຶກການແກ້ໄຂ
if (isset($_POST['update_submit'])) {
    $customer_name = $_POST['customer_name'];
    $invoice_date = $_POST['invoice_date'];
    $item_ids = $_POST['item_id'];
    $item_names = $_POST['item'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $updated_invoice_id = $_POST['invoice_id'];
    $subtotal = 0;
    $tax_rate = 0.10;
    $tax_amount = 0;
    $grand_total = 0;

    // ອັບເດດຂໍ້ມູນໃບບິນຫຼັກ
    $sql_update_invoice = "UPDATE invoices SET customer_name = ?, invoice_date = ? WHERE invoice_id = ?";
    $stmt_update_invoice = $conn->prepare($sql_update_invoice);
    $stmt_update_invoice->bind_param("ssi", $customer_name, $invoice_date, $updated_invoice_id);
    $stmt_update_invoice->execute();
    $stmt_update_invoice->close();

    // ລຶບລາຍການເກົ່າທັງໝົດກ່ອນທີ່ຈະເພີ່ມລາຍການໃໝ່
    $sql_delete_items = "DELETE FROM invoice_items WHERE invoice_id = ?";
    $stmt_delete_items = $conn->prepare($sql_delete_items);
    $stmt_delete_items->bind_param("i", $updated_invoice_id);
    $stmt_delete_items->execute();
    $stmt_delete_items->close();

    // ເພີ່ມລາຍການໃໝ່
    for ($i = 0; $i < count($item_names); $i++) {
        if (!empty($item_names[$i]) && is_numeric($quantities[$i]) && is_numeric($prices[$i])) {
            $item_name = $item_names[$i];
            $quantity = $quantities[$i];
            $price_per_unit = $prices[$i];
            $item_total = $quantity * $price_per_unit;
            $subtotal += $item_total;

            $sql_insert_item = "INSERT INTO invoice_items (invoice_id, item_name, quantity, price_per_unit, item_total)
                                VALUES (?, ?, ?, ?, ?)";
            $stmt_insert_item = $conn->prepare($sql_insert_item);
            $stmt_insert_item->bind_param("isidd", $updated_invoice_id, $item_name, $quantity, $price_per_unit, $item_total);
            $stmt_insert_item->execute();
            $stmt_insert_item->close();
        }
    }

    // ຄຳນວນລາຄາທັງໝົດຫຼັງຈາກອັບເດດລາຍການ
    $tax_amount = $subtotal * $tax_rate;
    $grand_total = $subtotal + $tax_amount;

    $sql_update_totals = "UPDATE invoices SET subtotal = ?, tax_amount = ?, grand_total = ? WHERE invoice_id = ?";
    $stmt_update_totals = $conn->prepare($sql_update_totals);
    $stmt_update_totals->bind_param("dddi", $subtotal, $tax_amount, $grand_total, $updated_invoice_id);
    $stmt_update_totals->execute();
    $stmt_update_totals->close();

    echo "<p style='color: green; text-align: center;'>ແກ້ໄຂໃບບິນສຳເລັດ! <a href='view_invoice.php?id=" . htmlspecialchars($updated_invoice_id) . "'>ເບິ່ງໃບບິນທີ່ແກ້ໄຂ</a></p>";
}

// ປິດການເຊື່ອມຕໍ່
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>ແກ້ໄຂໃບບິນເລກທີ: <?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <style>
        body { font-family: sans-serif; }
        .invoice-form { width: 600px; margin: 20px auto; border: 1px solid #ccc; padding: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 120px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"] { width: 200px; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .add-remove-buttons button { margin-right: 5px; cursor: pointer; }
        .item-input { margin-bottom: 10px; padding: 10px; border: 1px solid #eee; }
        .item-input label { display: inline-block; width: 80px; margin-right: 10px; }
        .item-input input[type="text"],
        .item-input input[type="number"] { width: 100px; padding: 5px; }
    </style>
</head>
<body>

    <div class="invoice-form">
        <h2>ແກ້ໄຂໃບບິນເລກທີ: <?php echo htmlspecialchars($invoice['invoice_number']); ?></h2>
        <form method="post">
            <input type="hidden" name="invoice_id" value="<?php echo htmlspecialchars($invoice_id); ?>">
            <div class="form-group">
                <label for="customer_name">ຊື່ລູກຄ້າ:</label>
                <input type="text" name="customer_name" value="<?php echo htmlspecialchars($invoice['customer_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="invoice_date">ວັນທີອອກບິນ:</label>
                <input type="date" name="invoice_date" value="<?php echo htmlspecialchars($invoice['invoice_date']); ?>" required>
            </div>

            <h3>ແກ້ໄຂລາຍການສິນຄ້າ/ບໍລິການ</h3>
            <div id="item-container">
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="item-input">
                            <input type="hidden" name="item_id[]" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                            <label for="item[]">ລາຍການ:</label>
                            <input type="text" name="item[]" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                            <label for="quantity[]">ຈຳນວນ:</label>
                            <input type="number" name="quantity[]" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" required>
                            <label for="price[]">ລາຄາ:</label>
                            <input type="number" name="price[]" step="0.01" value="<?php echo htmlspecialchars($item['price_per_unit']); ?>" min="0" required>
                            <div class="add-remove-buttons">
                                <button type="button" onclick="addItem(this)">ເພີ່ມ</button>
                                <button type="button" onclick="removeItem(this)">ລຶບ</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="item-input">
                        <label for="item[]">ລາຍການ:</label>
                        <input type="text" name="item[]" required>
                        <label for="quantity[]">ຈຳນວນ:</label>
                        <input type="number" name="quantity[]" value="1" min="1" required>
                        <label for="price[]">ລາຄາ:</label>
                        <input type="number" name="price[]" step="0.01" min="0" required>
                        <div class="add-remove-buttons">
                            <button type="button" onclick="addItem(this)">ເພີ່ມ</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <br>
            <button type="button" onclick="addItem(null)">ເພີ່ມລາຍການອື່ນ</button><br><br>
            <input type="submit" name="update_submit" value="ບັນທຶກການແກ້ໄຂ">
        </form>
        <p><a href="invoice_list.php">ກັບໄປລາຍການໃບບິນ</a></p