<?php
// ກຳນົດຂໍ້ມູນການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$servername = "localhost";
$username = "root";
$password = ""; // ລະຫັດຜ່ານເປັນຄ່າຫວ່າງເປົ່າເພາະໃນ phpMyAdmin ບອກວ່າ "No"
$dbname = "billing_db";

// ສ້າງການເຊື່ອມຕໍ່
$conn = new mysqli($servername, $username, $password, $dbname);

// ກວດສອບການເຊື່ອມຕໍ່
if ($conn->connect_error) {
    die("ການເຊື່ອມຕໍ່ຖານຂໍ້ມູນລົ້ມເຫລວ: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $customer_name = $_POST['customer_name'];
    $invoice_date = date("Y-m-d"); // ວັນທີປັດຈຸບັນ
    $items = $_POST['item'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $subtotal = 0;
    $tax_rate = 0.10;
    $tax_amount = 0;
    $grand_total = 0;
    $invoice_number = uniqid('INV-'); // ສ້າງເລກບິນແບບອັດຕະໂນມັດ (ສາມາດປັບປຸງໄດ້)

    // ຄຳນວນລວມຍ່ອຍ
    for ($i = 0; $i < count($items); $i++) {
        if (!empty($items[$i]) && is_numeric($quantities[$i]) && is_numeric($prices[$i])) {
            $subtotal += $quantities[$i] * $prices[$i];
        }
    }

    // ຄຳນວນອາກອນ ແລະລາຄາທັງໝົດ
    $tax_amount = $subtotal * $tax_rate;
    $grand_total = $subtotal + $tax_amount;

    // ບັນທຶກຂໍ້ມູນໃບບິນຫຼັກ
    $sql_invoice = "INSERT INTO invoices (invoice_number, customer_name, invoice_date, subtotal, tax_amount, grand_total)
                    VALUES ('$invoice_number', '$customer_name', '$invoice_date', $subtotal, $tax_amount, $grand_total)";

    if ($conn->query($sql_invoice) === TRUE) {
        $invoice_id = $conn->insert_id; // ເອົາ ID ໃບບິນທີ່ເພີ່ມເຂົ້າມາ

        // ບັນທຶກລາຍລະອຽດຂອງແຕ່ລະລາຍການ
        for ($i = 0; $i < count($items); $i++) {
            if (!empty($items[$i]) && is_numeric($quantities[$i]) && is_numeric($prices[$i])) {
                $item_name = $items[$i];
                $quantity = $quantities[$i];
                $price_per_unit = $prices[$i];
                $item_total = $quantity * $price_per_unit;

                $sql_item = "INSERT INTO invoice_items (invoice_id, item_name, quantity, price_per_unit, item_total)
                             VALUES ($invoice_id, '$item_name', $quantity, $price_per_unit, $item_total)";
                $conn->query($sql_item);
            }
        }
        echo "<p style='color: green;'>ບັນທຶກໃບບິນສຳເລັດ! ເລກບິນ: " . htmlspecialchars($invoice_number) . "</p>";
        // ສາມາດເພີ່ມການສະແດງໃບບິນທີ່ບັນທຶກໄວ້ໄດ້ທີ່ນີ້
    } else {
        echo "<p style='color: red;'>ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກໃບບິນ: " . $conn->error . "</p>";
    }
}

// ປິດການເຊື່ອມຕໍ່ຖານຂໍ້ມູນ
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>ລະບົບອອກບິນ (ເກັບຂໍ້ມູນ)</title>
    <style>
        body { font-family: sans-serif; }
        .invoice-form { width: 600px; margin: 20px auto; border: 1px solid #ccc; padding: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 120px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="number"] { width: 200px; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-row td { text-align: right; font-weight: bold; }
        .button-container { margin-bottom: 10px; }
        .item-input { margin-bottom: 10px; padding: 10px; border: 1px solid #eee; }
        .item-input label { display: inline-block; width: 80px; margin-right: 10px; }
        .item-input input[type="text"],
        .item-input input[type="number"] { width: 100px; padding: 5px; }
        .add-remove-buttons button { margin-right: 5px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="invoice-form">
        <h2>ສ້າງໃບບິນໃໝ່</h2>
        <form method="post">
            <div class="form-group">
                <label for="customer_name">ຊື່ລູກຄ້າ:</label>
                <input type="text" name="customer_name" required>
            </div>

            <h3>ລາຍການສິນຄ້າ/ບໍລິການ</h3>
            <div id="item-container">
                <div class="item-input">
                    <label for="item[]">ລາຍການ:</label>
                    <input type="text" name="item[]" required>
                    <label for="quantity[]">ຈຳນວນ:</label>
                    <input type="number" name="quantity[]" value="1" min="1" required>
                    <label for="price[]">ລາຄາ:</label>
                    <input type="number" name="price[]" step="0.01" min="0" required>
                    <div class="add-remove-buttons">
                        <button type="button" onclick="addItem()">ເພີ່ມ</button>
                    </div>
                </div>
            </div>
            <br>
            <button type="button" onclick="addItem()">ເພີ່ມລາຍການອື່ນ</button><br><br>
            <input type="submit" name="submit" value="ບັນທຶກໃບບິນ">
        </form>
    </div>

    <script>
        function addItem() {
            var container = document.getElementById("item-container");
            var newItemDiv = document.createElement("div");
            newItemDiv.classList.add("item-input");
            newItemDiv.innerHTML = `
                <label for="item[]">ລາຍການ:</label>
                <input type="text" name="item[]" required>
                <label for="quantity[]">ຈຳນວນ:</label>
                <input type="number" name="quantity[]" value="1" min="1" required>
                <label for="price[]">ລາຄາ:</label>
                <input type="number" name="price[]" step="0.01" min="0" required>
                <div class="add-remove-buttons">
                    <button type="button" onclick="addItem()">ເພີ່ມ</button>
                    <button type="button" onclick="removeItem(this)">ລຶບ</button>
                </div>
            `;
            container.appendChild(newItemDiv);
        }

        function removeItem(button) {
            var itemDiv = button.parentNode.parentNode; // Go up to the .item-input div
            itemDiv.parentNode.removeChild(itemDiv);
        }
    </script>

</body>
</html>