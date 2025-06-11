<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /lab4Bogdan/login.php');
    exit;
}

require_once '../db.php';

$tableNames = [
    'users' => 'Пользователи',
    'products' => 'Отели',
    'orders' => 'Бронирования'
];

$fieldNames = [
    'users' => [
        'id' => 'ID',
        'login' => 'Логин',
        'name' => 'Имя',
        'phone' => 'Телефон',
        'email' => 'Email',
    ],
    'products' => [
        'id' => 'ID',
        'title' => 'Название Отеля',
        'description' => 'Описание Отеля',
        'price' => 'Цена за ночь',
        'image' => 'Изображение'
    ],
    'orders' => [
        'id' => 'ID',
        'user_id' => 'ID клиента',
        'product_id' => 'ID отеля',
        'start_date' => 'Дата заезда',
        'end_date' => 'Дата выезда',
        'people' => 'Количество гостей',
        'order_date' => 'Дата бронирования'
    ]
];

$activeTable = $_GET['table'] ?? 'users';

if (isset($_POST['bulk_price_update'])) {
    $newPrice = (float)$_POST['new_price'];
    $ids = $_POST['hotel_ids'] ?? [];

    if (!empty($ids) && $newPrice > 0) {
        $idsStr = implode(',', array_map('intval', $ids));
        $conn->query("UPDATE products SET price = $newPrice WHERE id IN ($idsStr)");
    }
    header("Location: dashboard.php?table=products");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $table = $_GET['table'];
    $conn->query("DELETE FROM `$table` WHERE id = $id");
    header("Location: dashboard.php?table=$table");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save'])) {
        $table = $_POST['table'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

        $updates = [];
        foreach ($_POST as $key => $value) {
            if ($key !== 'table' && $key !== 'id' && $key !== 'save') {
                $updates[] = "`$key` = '" . $conn->real_escape_string($value) . "'";
            }
        }

        if (!empty($updates)) {
            if ($id) {
                $query = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE id = $id";
            } else {
                $columns = array_keys($_POST);
                $columns = array_filter($columns, function($key) {
                    return !in_array($key, ['table', 'id', 'save']);
                });

                // Исправление: сохраняем POST данные в локальную переменную перед использованием в замыкании
                $postData = $_POST;
                $values = array_map(function($key) use ($conn, $postData) {
                    return "'" . $conn->real_escape_string($postData[$key]) . "'";
                }, $columns);

                $query = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
            }
            $conn->query($query);
        }

        header("Location: dashboard.php?table=$table");
        exit;
    }
}

$users = $conn->query("SELECT id, login, name FROM users")->fetch_all(MYSQLI_ASSOC);
$hotels = $conn->query("SELECT id, title FROM products")->fetch_all(MYSQLI_ASSOC);

$tables = [
    'users' => array_keys($fieldNames['users']),
    'products' => array_keys($fieldNames['products']),
    'orders' => array_keys($fieldNames['orders'])
];

$tableData = [];
foreach ($tables as $tableName => $columns) {
    $result = $conn->query("SELECT * FROM `$tableName`");
    $tableData[$tableName] = [
        'columns' => $columns,
        'rows' => $result->fetch_all(MYSQLI_ASSOC)
    ];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background-color: #ffffff;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background: rgba(51, 82, 150, 0.24);
            margin-right: 5px;
            border: 1px solid #ccc;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
        }
        .tab.active {
            background: #15a498;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        .tab:hover {
            background: rgba(160, 248, 95, 0.96);
        }
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 100%;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }
        input, select, textarea {
            padding: 5px;
            box-sizing: border-box;
            width: 100%;
        }
        textarea {
            min-height: 60px;
            resize: vertical;
        }
        button, .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 2px;
        }
        .btn-edit {
            background-color: #619633;
            color: white;
        }
        .btn-save {
            background-color: #28a745;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        .btn-add {
            background-color: #17b870;
            color: white;
            padding: 8px 15px;
            margin-bottom: 15px;
        }
        .btn-logout {
            background-color: #343a40;
            color: white;
            padding: 8px 15px;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        .btn-bulk-update {
            background-color: #ffc107;
            color: black;
            padding: 8px 15px;
            margin-left: 10px;
        }
        .edit-row {
            background-color: #fffde7;
        }
        .search-filter {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .search-filter input, .search-filter select {
            padding: 8px;
            margin-right: 10px;
            width: auto;
        }
        .bulk-update-form {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }
        .checkbox-cell {
            width: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
<h2>Панель администратора</h2>
<a href="../logout.php" class="btn-logout">Выйти</a>

<div class="tabs">
    <?php foreach ($tableNames as $tableKey => $tableName): ?>
        <div class="tab <?= $activeTable === $tableKey ? 'active' : '' ?>"
             onclick="window.location.href='dashboard.php?table=<?= $tableKey ?>'">
            <?= $tableName ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="search-filter">
    <input type="text" id="searchInput" placeholder="Поиск..." onkeyup="filterTable()">
    <select id="filterColumn">
        <?php foreach ($fieldNames[$activeTable] as $key => $name): ?>
            <option value="<?= $key ?>"><?= $name ?></option>
        <?php endforeach; ?>
    </select>

    <?php if ($activeTable === 'products'): ?>
        <button class="btn-bulk-update" onclick="toggleBulkUpdate()">Массовое изменение цен</button>
    <?php endif; ?>
</div>

<?php if ($activeTable === 'products'): ?>
    <div class="bulk-update-form" id="bulkUpdateForm">
        <h3>Массовое изменение цен</h3>
        <form method="post" id="bulkPriceForm">
            <label for="new_price">Новая цена за ночь:</label>
            <input type="number" step="0.01" min="0" name="new_price" id="new_price" required>
            <button type="submit" name="bulk_price_update" class="btn-save">Применить</button>
            <button type="button" class="btn-cancel" onclick="toggleBulkUpdate()">Отмена</button>
        </form>
    </div>
<?php endif; ?>

<button class="btn-add" onclick="addNewRow()">+ Добавить запись</button>

<table id="dataTable">
    <thead>
    <tr>
        <?php if ($activeTable === 'products'): ?>
            <th class="checkbox-cell">
                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
            </th>
        <?php endif; ?>

        <?php foreach ($fieldNames[$activeTable] as $key => $name): ?>
            <th><?= $name ?></th>
        <?php endforeach; ?>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($tableData[$activeTable]['rows'] as $row): ?>
        <tr id="row-<?= $row['id'] ?>">
            <?php if ($activeTable === 'products'): ?>
                <td class="checkbox-cell">
                    <input type="checkbox" class="hotel-checkbox" name="hotel_ids[]" value="<?= $row['id'] ?>">
                </td>
            <?php endif; ?>

            <?php foreach ($tableData[$activeTable]['columns'] as $column): ?>
                <td class="view-mode" data-column="<?= $column ?>">
                    <?= htmlspecialchars($row[$column]) ?>
                </td>
                <td class="edit-mode" style="display:none;">
                    <?php if ($column === 'id'): ?>
                        <?= $row['id'] ?>
                    <?php elseif ($column === 'password'): ?>
                        <input type="password" name="<?= $column ?>" value="<?= htmlspecialchars($row[$column]) ?>">
                    <?php elseif (strpos($column, 'date') !== false): ?>
                        <input type="date" name="<?= $column ?>" value="<?= htmlspecialchars($row[$column]) ?>">
                    <?php elseif ($column === 'description'): ?>
                        <textarea name="<?= $column ?>"><?= htmlspecialchars($row[$column]) ?></textarea>
                    <?php elseif (($activeTable === 'orders' && in_array($column, ['user_id', 'product_id']))): ?>
                        <select name="<?= $column ?>">
                            <?php
                            $options = $column === 'user_id' ? $users : $hotels;
                            foreach ($options as $option):
                                $selected = $option['id'] == $row[$column] ? 'selected' : '';
                                $display = $column === 'user_id'
                                    ? ($option['name'] ?: $option['login'])
                                    : $option['title'];
                                ?>
                                <option value="<?= $option['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($display) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="<?= $column ?>" value="<?= htmlspecialchars($row[$column]) ?>">
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
            <td>
                <div class="view-mode">
                    <button class="btn-edit" onclick="editRow(<?= $row['id'] ?>)">Редактировать</button>
                    <button class="btn-delete" onclick="deleteRow(<?= $row['id'] ?>, '<?= $activeTable ?>')">Удалить</button>
                </div>
                <div class="edit-mode" style="display:none;">
                    <button class="btn-save" onclick="saveRow(<?= $row['id'] ?>, '<?= $activeTable ?>')">Сохранить</button>
                    <button class="btn-cancel" onclick="cancelEdit(<?= $row['id'] ?>)">Отмена</button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    const users = <?= json_encode($users) ?>;
    const hotels = <?= json_encode($hotels) ?>;
    const activeTable = '<?= $activeTable ?>';

    function editRow(id) {
        const row = document.getElementById(`row-${id}`);
        row.classList.add('edit-row');

        const viewCells = row.querySelectorAll('.view-mode');
        const editCells = row.querySelectorAll('.edit-mode');

        viewCells.forEach(cell => cell.style.display = 'none');
        editCells.forEach(cell => cell.style.display = '');

        if (activeTable === 'orders') {
            const userIdCell = row.querySelector('[data-column="user_id"]').nextElementSibling;
            const productIdCell = row.querySelector('[data-column="product_id"]').nextElementSibling;

            if (userIdCell && !userIdCell.querySelector('select')) {
                createDropdown(userIdCell, 'user_id', users, id, 'user_id');
            }

            if (productIdCell && !productIdCell.querySelector('select')) {
                createDropdown(productIdCell, 'product_id', hotels, id, 'product_id');
            }
        }
    }

    function createDropdown(cell, name, options, rowId, column) {
        const currentValue = document.querySelector(`#row-${rowId} [data-column="${column}"]`).textContent;
        const select = document.createElement('select');
        select.name = name;

        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.id;
            opt.textContent = name === 'user_id'
                ? (option.name || option.login)
                : option.title;
            opt.selected = (option.id == currentValue);
            select.appendChild(opt);
        });

        cell.innerHTML = '';
        cell.appendChild(select);
    }

    function cancelEdit(id) {
        const row = document.getElementById(`row-${id}`);
        row.classList.remove('edit-row');

        const viewCells = row.querySelectorAll('.view-mode');
        const editCells = row.querySelectorAll('.edit-mode');

        viewCells.forEach(cell => cell.style.display = '');
        editCells.forEach(cell => cell.style.display = 'none');
    }

    function saveRow(id, table) {
        const row = document.getElementById(`row-${id}`);
        const inputs = row.querySelectorAll('.edit-mode input, .edit-mode textarea, .edit-mode select');

        const formData = new FormData();
        formData.append('table', table);
        formData.append('id', id);
        formData.append('save', '1');

        inputs.forEach(input => {
            if (input.tagName === 'SELECT') {
                formData.append(input.name, input.value);
            } else {
                formData.append(input.name, input.value);
            }
        });

        fetch('dashboard.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                }
            });
    }

    function deleteRow(id, table) {
        if (confirm('Вы уверены, что хотите удалить эту запись?')) {
            window.location.href = `dashboard.php?delete=${id}&table=${table}`;
        }
    }

    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toUpperCase();
        const columnSelect = document.getElementById("filterColumn");
        const columnIndex = Array.from(columnSelect.options).findIndex(option => option.value === columnSelect.value);
        const offset = activeTable === 'products' ? 1 : 0;

        const table = document.getElementById("dataTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            const td = tr[i].getElementsByTagName("td")[columnIndex + offset];
            if (td) {
                const txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    function addNewRow() {
        const table = document.getElementById("dataTable");
        const tbody = table.querySelector("tbody");
        const columns = <?= json_encode($tableData[$activeTable]['columns']) ?>;
        const hasCheckbox = activeTable === 'products';

        const newRow = document.createElement("tr");
        newRow.classList.add("edit-row");

        if (hasCheckbox) {
            const checkboxCell = document.createElement("td");
            checkboxCell.className = "checkbox-cell";
            newRow.appendChild(checkboxCell);
        }

        columns.forEach(column => {
            const viewCell = document.createElement("td");
            viewCell.className = "view-mode";
            viewCell.style.display = "none";
            viewCell.setAttribute("data-column", column);
            viewCell.textContent = "Новая запись";
            newRow.appendChild(viewCell);

            const editCell = document.createElement("td");
            editCell.className = "edit-mode";

            let input;
            if (column === 'id') {
                editCell.textContent = "Новая запись";
            } else if (column === 'password') {
                input = document.createElement("input");
                input.type = "password";
                input.name = column;
                editCell.appendChild(input);
            } else if (column.includes('date')) {
                input = document.createElement("input");
                input.type = "date";
                input.name = column;
                editCell.appendChild(input);
            } else if (column === 'description') {
                input = document.createElement("textarea");
                input.name = column;
                editCell.appendChild(input);
            } else if (activeTable === 'orders' && (column === 'user_id' || column === 'product_id')) {
                const select = document.createElement('select');
                select.name = column;

                const options = column === 'user_id' ? users : hotels;
                options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.id;
                    opt.textContent = column === 'user_id'
                        ? (option.name || option.login)
                        : option.title;
                    select.appendChild(opt);
                });

                editCell.appendChild(select);
            } else {
                input = document.createElement("input");
                input.type = "text";
                input.name = column;
                editCell.appendChild(input);
            }

            newRow.appendChild(editCell);
        });

        const actionCell = document.createElement("td");

        const saveDiv = document.createElement("div");
        saveDiv.className = "edit-mode";

        const saveButton = document.createElement("button");
        saveButton.className = "btn-save";
        saveButton.textContent = "Сохранить";
        saveButton.onclick = function() { saveNewRow(this); };

        const cancelButton = document.createElement("button");
        cancelButton.className = "btn-cancel";
        cancelButton.textContent = "Отмена";
        cancelButton.onclick = function() { tbody.removeChild(newRow); };

        saveDiv.appendChild(saveButton);
        saveDiv.appendChild(cancelButton);
        actionCell.appendChild(saveDiv);
        newRow.appendChild(actionCell);

        tbody.insertBefore(newRow, tbody.firstChild);
    }

    function saveNewRow(button) {
        const row = button.closest("tr");
        const inputs = row.querySelectorAll('.edit-mode input, .edit-mode textarea, .edit-mode select');

        const formData = new FormData();
        formData.append('table', activeTable);
        formData.append('save', '1');

        inputs.forEach(input => {
            if (input.name) {
                formData.append(input.name, input.value);
            }
        });

        fetch('dashboard.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                }
            });
    }

    function toggleBulkUpdate() {
        const form = document.getElementById('bulkUpdateForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.hotel-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const bulkForm = document.getElementById('bulkPriceForm');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                const selected = document.querySelectorAll('.hotel-checkbox:checked');
                if (selected.length === 0) {
                    e.preventDefault();
                    alert('Выберите хотя бы один отель');
                    return false;
                }

                selected.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'hotel_ids[]';
                    input.value = checkbox.value;
                    bulkForm.appendChild(input);
                });

                return true;
            });
        }
    });
</script>
</body>
</html>