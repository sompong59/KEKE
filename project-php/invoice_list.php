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

// ກວດສອບການລຶບບິນ
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM invoices WHERE invoice_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_id);
    if ($stmt_delete->execute()) {
        echo "<p style='color: green; text-align: center;'>ລຶບບິນສຳເລັດ.</p>";
    } else {
        echo "<p style='color: red; text-align: center;'>ເກີດຂໍ້ຜິດພາດໃນການລຶບບິນ: " . $conn->error . "</p>";
    }
    $stmt_delete->close();
}

// ດຶງຂໍ້ມູນໃບບິນທັງໝົດ
$sql = "SELECT invoice_id, invoice_number, customer_name, invoice_date, grand_total FROM invoices ORDER BY invoice_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>ລາຍການໃບບິນ</title>
    <style>
        body { font-family: sans-serif; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 80%; margin: 0 auto; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .action-buttons a { background-color: #008CBA; color: white; border: none; padding: 8px 12px; text-align: center; text-decoration: none; display: inline-block; font-size: 14px; cursor: pointer; border-radius: 5px; margin-right: 5px; }
        .action-buttons a.delete { background-color: #f44336; }
        .action-buttons a.view { background-color:rgb(0, 190, 41); }
        
    </style>
</head>
<body>

    <h2>ລາຍການໃບບິນ</h2>

    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<thead><tr><th>ເລກບິນ</th><th>ຊື່ລູກຄ້າ</th><th>ວັນທີອອກບິນ</th><th>ລາຄາທັງໝົດ</th><th>ການກະທຳ</th></tr></thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["invoice_number"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["customer_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["invoice_date"]) . "</td>";
            echo "<td style='text-align: right;'>" . htmlspecialchars(number_format($row["grand_total"], 2)) . "</td>";
            echo "<td class='action-buttons'>";
            echo "<a href='view_invoice.php?id=" . htmlspecialchars($row["invoice_id"]) . "' class='view'>ເບິ່ງ</a>";
            echo "<a href='edit_invoice.php?id=" . htmlspecialchars($row["invoice_id"]) . "'>ແກ້ໄຂ</a>";
            echo "<a href='invoice_list.php?delete_id=" . htmlspecialchars($row["invoice_id"]) . "' class='delete' onclick='return confirm(\"ທ່ານແນ່ໃຈບໍວ່າຕ້ອງການລຶບບິນນີ້?\")'>ລຶບ</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p style='text-align: center;'>ບໍ່ມີໃບບິນທີ່ຖືກສ້າງເທື່ອ.</p>";
    }

    // ປິດການເຊື່ອມຕໍ່
    $conn->close();
    ?>

</body>
</html>