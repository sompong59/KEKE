<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemName = $_POST["itemName"];
    $price = $_POST["price"];
    $taxRate = $_POST["taxRate"];

    // ຄຳນວນມູນຄ່າອາກອນ
    $taxAmount = ($price * ($taxRate / 100));

    // ຄຳນວນລາຄາລວມ
    $totalAmount = $price + $taxAmount;

    echo "<h2>ໃບອາກອນ</h2>";
    echo "<p>ຊື່ສິນຄ້າ: " . htmlspecialchars($itemName) . "</p>";
    echo "<p>ລາຄາ: " . number_format($price, 2) . " ກີບ</p>";
    echo "<p>ອັດຕາອາກອນ: " . number_format($taxRate, 2) . "%</p>";
    echo "<p>ມູນຄ່າອາກອນ: " . number_format($taxAmount, 2) . " ກີບ</p>";
    echo "<h3>ລາຄາລວມ: " . number_format($totalAmount, 2) . " ກີບ</h3>";
} else {
    echo "ເກີດຂໍ້ຜິດພາດ: ບໍ່ມີຂໍ້ມູນຖືກສົ່ງມາ.";
}

?>