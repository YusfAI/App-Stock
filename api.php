<?php
// ====== DB CONFIG ======
// Override with environment variables in production (never commit real passwords).
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'el_buteur';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: ''; // XAMPP default: empty password

// ====== BUSINESS SETTINGS ======
// Cash available before the first purchase/sale was recorded in the app (TND).
const INITIAL_CAPITAL = 862;
// Aramex deliveries made before the app was used, excluded from the delivery counter.
const ARAMEX_COUNTER_OFFSET = 4;

header('Content-Type: application/json; charset=utf-8');

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed', 'details' => $e->getMessage()]);
  exit;
}

function json_input(): array {
  $raw = file_get_contents('php://input') ?: '';
  if ($raw === '') return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

$entity = $_GET['entity'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Override via query (compat fetch/hosting)
if ($method === 'POST' && isset($_GET['_method'])) {
  $method = strtoupper($_GET['_method']);
}

try {
  switch ($entity) {
    /* ======================= DASHBOARD ======================= */
    case 'dashboard':
      if ($method === 'GET') {
        // Get total sales count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM sales");
        $totalSales = $stmt->fetch()['total'];
        
        // Get total products count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products where category != '0' ");
        $totalProducts = $stmt->fetch()['total'];
        
        // Get total suppliers count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM suppliers");
        $totalSuppliers = $stmt->fetch()['total'];
        
        // Get total revenue (sum of all sales)
        $stmt = $pdo->query("SELECT SUM(sell_price * quantity) as total FROM sales");
        $totalRevenue = $stmt->fetch()['total'] ?? 0;
        

// ===== NEW KPI: Monthly Profit (current month)
$stmt = $pdo->query("
  SELECT SUM((s.sell_price - p.buy_price) * s.quantity) AS total
  FROM sales s
  JOIN products p ON s.product_id = p.product_id
  WHERE s.sale_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    AND s.sale_date <= LAST_DAY(CURDATE())
");
$monthlyProfit = $stmt->fetch()['total'] ?? 0;

// ===== NEW KPI: Previous Month Profit
$stmt = $pdo->query("
  SELECT SUM((s.sell_price - p.buy_price) * s.quantity) AS total
  FROM sales s
  JOIN products p ON s.product_id = p.product_id
  WHERE s.sale_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
    AND s.sale_date <= LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
");
$previousMonthProfit = $stmt->fetch()['total'] ?? 0;

// ===== UPDATED KPI: Weekly Aramex Sales (Sat→Fri; resets Friday night)
$stmt = $pdo->query("
  SELECT SUM(sell_price * quantity) AS total
  FROM sales
  WHERE shipping_method = 'Aramex'
    AND sale_date >= DATE_SUB(CURDATE(), INTERVAL ((WEEKDAY(CURDATE()) + 2) % 7) DAY)
    AND sale_date <  DATE_ADD(DATE_SUB(CURDATE(), INTERVAL ((WEEKDAY(CURDATE()) + 2) % 7) DAY), INTERVAL 7 DAY)
");
$weeklyAramex = $stmt->fetch()['total'] ?? 0;

// Previous week Aramex (Sat→Fri)
$stmt = $pdo->query("
  SELECT SUM(sell_price * quantity) AS total
  FROM sales
  WHERE shipping_method = 'Aramex'
    AND sale_date >= DATE_SUB(CURDATE(), INTERVAL ((WEEKDAY(CURDATE()) + 2) % 7) + 7 DAY)
    AND sale_date <  DATE_SUB(CURDATE(), INTERVAL ((WEEKDAY(CURDATE()) + 2) % 7) DAY)
");
$prevWeekAramex = $stmt->fetch()['total'] ?? 0;

// ===== NEW KPI: All-time Aramex sales counter
$stmt = $pdo->query("SELECT GREATEST(COUNT(*) - ".ARAMEX_COUNTER_OFFSET.", 0) AS c FROM sales WHERE shipping_method = 'Aramex'");
$aramexCounter = $stmt->fetch()['c'] ?? 0;
        
        // Get total spending (sum of all supplier purchases)
        $stmt = $pdo->query("SELECT SUM(buy_price * quantity) as total FROM suppliers");
        $totalSpending = $stmt->fetch()['total'] ?? 0;
        
        // FIXED: Calculate net profit correctly (revenue from sales minus cost of sold items)
        $stmt = $pdo->query("SELECT SUM((s.sell_price - p.buy_price) * s.quantity) as total_profit 
                            FROM sales s 
                            JOIN products p ON s.product_id = p.product_id");
        $netProfit = $stmt->fetch()['total_profit'] ?? 0;
        
        // FIXED: Calculate current money as total revenue minus total spending
        $currentMoney = INITIAL_CAPITAL + $totalRevenue - $totalSpending;
        
        // MODIFIED: Get recent sales with phone number included
        $stmt = $pdo->query("SELECT s.*, CONCAT(customer_firstname, ' ', customer_lastname) as customer 
                            FROM sales s ORDER BY sale_id DESC LIMIT 5");
        $recentSales = $stmt->fetchAll();
        
        echo json_encode([
          'totalSales' => $totalSales,
          'totalProducts' => $totalProducts,
          'totalSuppliers' => $totalSuppliers,
          'totalRevenue' => $totalRevenue,
          'weeklyAramex' => $weeklyAramex,
          'totalSpending' => $totalSpending,
          'netProfit' => $netProfit,
          'currentMoney' => $currentMoney, // Fixed: total revenue minus total spending
          'monthlyProfit' => $monthlyProfit,
          'previousMonthProfit' => $previousMonthProfit,
          'prevWeekAramex' => $prevWeekAramex,
          'aramexCounter' => $aramexCounter,
          'isFriday' => ((int)date('N') === 5),
          'isLastDayOfMonth' => (date('Y-m-d') === date('Y-m-t')),
          'today' => date('Y-m-d'),
          'recentSales' => $recentSales
        ]);
        exit;
      }
      http_response_code(405);
      echo json_encode(['error'=>"Method not allowed for 'dashboard'"]);
      exit;

    /* ======================= PRODUCTS ======================= */
    case 'products':
      if ($method === 'GET') {
        $stmt = $pdo->query("SELECT product_id, category, name, size, supplier, date_added, quantity, buy_price, sell_price, profit_per_unit FROM products ORDER BY product_id DESC");
        echo json_encode($stmt->fetchAll());
        exit;
      }

      if ($method === 'POST') {
        $in = json_input();
        $category   = $in['category']   ?? '';
        $name       = $in['name']       ?? '';
        $size       = $in['size']       ?? null;
        $supplier   = $in['supplier']   ?? null;
        $quantity   = isset($in['quantity']) ? (int)$in['quantity'] : 0;
        $buy_price  = $in['buy_price']  ?? null;
        $sell_price = $in['sell_price'] ?? null;

        if ($category === '' || $name === '' || $buy_price === null || $sell_price === null) {
          http_response_code(400);
          echo json_encode(['error' => 'Missing required fields: category, name, buy_price, sell_price']);
          exit;
        }

        $stmt = $pdo->prepare("INSERT INTO products (category, name, size, supplier, quantity, buy_price, sell_price) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$category, $name, $size, $supplier, $quantity, $buy_price, $sell_price]);

        $id  = (int)$pdo->lastInsertId();
        $row = $pdo->query("SELECT * FROM products WHERE product_id = $id")->fetch();
        echo json_encode($row);
        exit;
      }

      if ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

        $in = json_input();
        // Champs autorisés à la mise à jour
        $allowed = ['category','name','size','supplier','quantity','buy_price','sell_price'];
        $sets = []; $vals = [];
        foreach ($allowed as $k) {
          if (array_key_exists($k, $in)) {
            $sets[] = "$k = ?";
            $vals[] = $in[$k];
          }
        }
        if (empty($sets)) { echo json_encode(['ok'=>true]); exit; }

        $vals[] = $id;
        $sql = "UPDATE products SET ".implode(', ',$sets)." WHERE product_id = ?";
        $stmt = $pdo->prepare($sql); $stmt->execute($vals);

        $row = $pdo->query("SELECT * FROM products WHERE product_id = $id")->fetch();
        echo json_encode($row);
        exit;
      }

      if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

        // Tentative suppression (échouera si des ventes existent, selon FK)
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$id]);

        echo json_encode(['ok'=>true, 'deleted_id'=>$id]);
        exit;
      }

      http_response_code(405);
      echo json_encode(['error'=>"Method not allowed for 'products'"]);
      exit;

    /* ======================= SALES ========================== */
    case 'sales':
      if ($method === 'GET') {
        $stmt = $pdo->query("SELECT sale_id, customer_firstname, customer_lastname, phone, address, product_id, product_name, size, quantity, buy_price, sell_price, total_profit, sale_date, shipping_method, notes FROM sales ORDER BY sale_id DESC");
        echo json_encode($stmt->fetchAll());
        exit;
      }

      if ($method === 'POST') {
        $in = json_input();
        $required = ['customer_firstname','customer_lastname','product_id','product_name','quantity','buy_price','sell_price','shipping_method'];
        foreach ($required as $r) {
          if (!isset($in[$r]) || $in[$r]==='') {
            http_response_code(400);
            echo json_encode(['error'=>"Missing field '$r'"]);
            exit;
          }
        }
        // Normaliser date (si non fournie)
        $sale_date = $in['sale_date'] ?? date('Y-m-d');

        $stmt = $pdo->prepare("INSERT INTO sales
          (customer_firstname, customer_lastname, phone, address, product_id, product_name, size, quantity, buy_price, sell_price, sale_date, shipping_method, notes)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
          $in['customer_firstname'], $in['customer_lastname'], $in['phone'] ?? null, $in['address'] ?? null,
          (int)$in['product_id'], $in['product_name'], $in['size'] ?? null, (int)$in['quantity'],
          $in['buy_price'], $in['sell_price'], $sale_date, $in['shipping_method'], $in['notes'] ?? null
        ]);

        $id  = (int)$pdo->lastInsertId();
        $row = $pdo->query("SELECT * FROM sales WHERE sale_id = $id")->fetch();
        echo json_encode($row);
        exit;
      }

      if ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

        $in = json_input();
        $allowed = ['customer_firstname','customer_lastname','phone','address','product_id','product_name','size','quantity','buy_price','sell_price','sale_date','shipping_method','notes'];
        $sets = []; $vals=[];
        foreach($allowed as $k){ if(array_key_exists($k,$in)){ $sets[]="$k = ?"; $vals[]=$in[$k]; } }
        if(empty($sets)){ echo json_encode(['ok'=>true]); exit; }
        $vals[] = $id;

        $sql = "UPDATE sales SET ".implode(', ',$sets)." WHERE sale_id = ?";
        $stmt = $pdo->prepare($sql); $stmt->execute($vals);

        $row = $pdo->query("SELECT * FROM sales WHERE sale_id = $id")->fetch();
        echo json_encode($row);
        exit;
      }

      if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

        $stmt = $pdo->prepare("DELETE FROM sales WHERE sale_id = ?");
        $stmt->execute([$id]);

        echo json_encode(['ok'=>true, 'deleted_id'=>$id]);
        exit;
      }

      http_response_code(405);
      echo json_encode(['error'=>"Method not allowed for 'sales'"]);
      exit;

    /* ======================= SUPPLIERS ====================== */
    case 'suppliers':
      if ($method === 'GET') {
        $stmt = $pdo->query("SELECT id_supp, num_fact, name, adresse, page, phone, date, product, sizes, buy_price, payment_method, quantity FROM suppliers ORDER BY id_supp DESC");
        echo json_encode($stmt->fetchAll());
        exit;
      }

      if ($method === 'POST') {
        $in = json_input();
        if (!isset($in['name']) || !isset($in['product'])) {
          http_response_code(400);
          echo json_encode(['error'=>"Missing field 'name' or 'product'"]);
          exit;
        }
        $stmt = $pdo->prepare("INSERT INTO suppliers
          (num_fact, name, adresse, page, phone, date, product, sizes, buy_price, payment_method, quantity)
          VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
          $in['num_fact'] ?? null,
          $in['name'],
          $in['adresse'] ?? null,
          $in['page'] ?? null,
          $in['phone'] ?? null,
          $in['date'] ?? null,      // attention aux modes SQL strict + dates nulles
          $in['product'],
          $in['sizes'] ?? null,
          $in['buy_price'] ?? 0,
          $in['payment_method'] ?? 'Cash',
          isset($in['quantity']) ? (int)$in['quantity'] : 0,
        ]);

        $id  = (int)$pdo->lastInsertId();
        $row = $pdo->query("SELECT * FROM suppliers WHERE id_supp = $id")->fetch();
        echo json_encode($row);
        exit;
      }

      if ($method === 'PUT') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

        $in = json_input();
        $allowed = ['num_fact','name','adresse','page','phone','date','product','sizes','buy_price','payment_method','quantity'];
        $sets = []; $vals=[];
        foreach($allowed as $k){ if(array_key_exists($k,$in)){ $sets[]="$k = ?"; $vals[]=$in[$k]; } }
        if(empty($sets)){ echo json_encode(['ok'=>true]); exit; }
        $vals[] = $id;
        $sql = "UPDATE suppliers SET ".implode(', ',$sets)." WHERE id_supp = ?";
        $stmt = $pdo->prepare($sql); $stmt->execute($vals);

        $row = $pdo->query("SELECT * FROM suppliers WHERE id_supp = $id")->fetch();
        echo json_encode($row);
        exit;
      }

      if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id_supp = ?");
        $stmt->execute([$id]);

        echo json_encode(['ok'=>true, 'deleted_id'=>$id]);
        exit;
      }

      http_response_code(405);
      echo json_encode(['error'=>"Method not allowed for 'suppliers'"]);
      exit;

          /* ======================= MONTHLY KPIS ======================= */
    case 'monthly_kpis':
      if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM monthly_kpis ORDER BY id");
        echo json_encode($stmt->fetchAll());
        exit;
      }

      if ($method === 'POST') {
        $in = json_input();
        
        // Validate required fields
        $required = ['month_year', 'sales_count', 'delivery_count', 'products_count', 
                    'total_revenue', 'net_profit', 'monthly_profit', 'total_spending', 'current_money'];
        foreach ($required as $field) {
          if (!isset($in[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $field"]);
            exit;
          }
        }

        $values = [
          $in['sales_count'], $in['delivery_count'], $in['products_count'],
          $in['total_revenue'], $in['net_profit'], $in['monthly_profit'],
          $in['total_spending'], $in['current_money'],
        ];

        // One row per month: the dashboard asks the user before overwriting, so update if it exists
        $stmt = $pdo->prepare("SELECT id FROM monthly_kpis WHERE month_year = ?");
        $stmt->execute([$in['month_year']]);
        $existing = $stmt->fetch();

        if ($existing) {
          $stmt = $pdo->prepare("UPDATE monthly_kpis SET
            sales_count = ?, delivery_count = ?, products_count = ?, total_revenue = ?,
            net_profit = ?, monthly_profit = ?, total_spending = ?, current_money = ?
            WHERE id = ?");
          $stmt->execute([...$values, $existing['id']]);
          $id = (int)$existing['id'];
        } else {
          $stmt = $pdo->prepare("INSERT INTO monthly_kpis
            (month_year, sales_count, delivery_count, products_count, total_revenue,
             net_profit, monthly_profit, total_spending, current_money)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
          $stmt->execute([$in['month_year'], ...$values]);
          $id = (int)$pdo->lastInsertId();
        }

        $row = $pdo->query("SELECT * FROM monthly_kpis WHERE id = $id")->fetch();
        echo json_encode($row);
        exit;
      }

      http_response_code(405);
      echo json_encode(['error' => "Method not allowed for 'monthly_kpis'"]);
      exit;

    default:
      http_response_code(404);
      echo json_encode(['error'=>'Unknown entity']);
      exit;
  }
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['error'=>'Request failed', 'details'=>$e->getMessage()]);
}