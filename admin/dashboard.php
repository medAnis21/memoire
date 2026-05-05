<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Guard: admin only
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/index.php');
    exit;
}

require_once __DIR__ . '/../include/db.php';

$flash = [];

// ── AJAX / POST actions ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* Accept / Reject a stagaire */
    if ($action === 'set_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $notes  = trim($_POST['notes'] ?? '');
        if ($id && in_array($status, ['accepted','rejected','pending'])) {
            $stmt = $conn->prepare("UPDATE stagaires SET status=:s, admin_notes=:n WHERE id=:id");
            $stmt->execute([':s' => $status, ':n' => $notes, ':id' => $id]);

            // On acceptance, optionally e-mail branch manager
            if ($status === 'accepted') {
                $row = $conn->prepare("SELECT s.*, bq.manager_email, bq.manager_name FROM stagaires s LEFT JOIN branch_quotas bq ON bq.branch=s.branch WHERE s.id=:id");
                $row->execute([':id' => $id]);
                $r = $row->fetch(PDO::FETCH_ASSOC);
                if ($r && $r['manager_email']) {
                    $subj = "[Sonatrach] Nouveau stagaire accepté – " . $r['branch'];
                    $body = "Bonjour {$r['manager_name']},\n\nUn nouveau stagaire a été accepté pour votre branche {$r['branch']}.\n\nNom : {$r['first_name']} {$r['last_name']}\nUniversité : {$r['university']}\nSpécialité : {$r['speciality']}\nEmail : {$r['email']}\nTél : {$r['phone']}\n\nCordialement,\nAdministration Sonatrach";
                    @mail($r['manager_email'], $subj, $body, "From: admin@sonatrach.dz\r\nContent-Type: text/plain; charset=utf-8\r\n");
                }
            }
            $flash = ['type'=>'success','msg'=>'Statut mis à jour avec succès.'];
        }
    }

    /* Update quota or manager for a branch */
    if ($action === 'update_quota') {
        $branch        = $_POST['branch'] ?? '';
        $quota         = (int)($_POST['max_quota'] ?? 0);
        $mgr_email     = trim($_POST['manager_email'] ?? '');
        $mgr_name      = trim($_POST['manager_name'] ?? '');
        if ($branch) {
            $stmt = $conn->prepare("INSERT INTO branch_quotas (branch, max_quota, manager_email, manager_name)
                VALUES (:b,:q,:me,:mn)
                ON DUPLICATE KEY UPDATE max_quota=:q2, manager_email=:me2, manager_name=:mn2");
            $stmt->execute([
                ':b'=>$branch,':q'=>$quota,':me'=>$mgr_email,':mn'=>$mgr_name,
                ':q2'=>$quota,':me2'=>$mgr_email,':mn2'=>$mgr_name
            ]);
            $flash = ['type'=>'success','msg'=>"Quota de la branche $branch mis à jour."];
        }
    }

    // Redirect to avoid re-POST on refresh
    $_SESSION['flash'] = $flash;
    header('Location: dashboard.php' . (isset($_GET['branch']) ? '?branch='.urlencode($_GET['branch']) : ''));
    exit;
}

if (!empty($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); }

// ── READ DATA ───────────────────────────────────────────────────────────────
$filterBranch  = $_GET['branch']  ?? '';
$filterStatus  = $_GET['status']  ?? '';
$filterSearch  = trim($_GET['q']  ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 12;

$where  = [];
$params = [];
if ($filterBranch) { $where[] = 's.branch = :branch'; $params[':branch'] = $filterBranch; }
if ($filterStatus) { $where[] = 's.status = :status'; $params[':status'] = $filterStatus; }
if ($filterSearch) {
    $where[] = "(s.first_name LIKE :q OR s.last_name LIKE :q OR s.email LIKE :q OR s.university LIKE :q)";
    $params[':q'] = '%' . $filterSearch . '%';
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalStmt = $conn->prepare("SELECT COUNT(*) FROM stagaires s $whereSQL");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare("SELECT s.*, bq.max_quota, bq.manager_email, bq.manager_name FROM stagaires s LEFT JOIN branch_quotas bq ON bq.branch = s.branch $whereSQL ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$stagaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stats = $conn->query("SELECT
    COUNT(*) as total,
    SUM(status='pending') as pending,
    SUM(status='accepted') as accepted,
    SUM(status='rejected') as rejected
FROM stagaires")->fetch(PDO::FETCH_ASSOC);

// Branch summary
$branchSummary = $conn->query("
    SELECT s.branch,
           COUNT(*) as total,
           SUM(s.status='accepted') as accepted,
           SUM(s.status='pending') as pending,
           SUM(s.status='rejected') as rejected,
           bq.max_quota,
           bq.manager_email,
           bq.manager_name
    FROM stagaires s
    LEFT JOIN branch_quotas bq ON bq.branch = s.branch
    GROUP BY s.branch
    ORDER BY s.branch
")->fetchAll(PDO::FETCH_ASSOC);

// All quotas (including branches with 0 stagaires)
$allQuotas = $conn->query("SELECT * FROM branch_quotas ORDER BY branch")->fetchAll(PDO::FETCH_ASSOC);

$branches = ['CP2K','CP1K','GL1K','RA1K','RA2K','GL1Z','GL2Z','GP1Z','GNL'];

function buildQuery(array $merge = [], array $remove = []): string {
    $p = $_GET;
    unset($p['page']);
    foreach ($remove as $k) unset($p[$k]);
    $p = array_merge($p, $merge);
    return $p ? '?' . http_build_query($p) : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sonatrach — Administration</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="dashboard.css"/>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <span class="logo-mark">S</span>
    <span class="logo-text">SONATRACH</span>
  </div>

  <nav class="sidebar-nav">
    <span class="nav-section-label">Tableau de bord</span>
    <a href="dashboard.php" class="nav-item <?= (!$filterBranch && !$filterStatus) ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Vue générale
    </a>
    <a href="dashboard.php<?= buildQuery(['status'=>'pending']) ?>" class="nav-item <?= ($filterStatus==='pending' && !$filterBranch) ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
      En attente
      <?php if ($stats['pending']): ?><span class="badge-count"><?= $stats['pending'] ?></span><?php endif; ?>
    </a>
    <a href="dashboard.php<?= buildQuery(['status'=>'accepted']) ?>" class="nav-item <?= ($filterStatus==='accepted' && !$filterBranch) ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
      Acceptés
    </a>
    <a href="dashboard.php<?= buildQuery(['status'=>'rejected']) ?>" class="nav-item <?= ($filterStatus==='rejected' && !$filterBranch) ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      Rejetés
    </a>

    <span class="nav-section-label" style="margin-top:1.5rem;">Branches</span>
    <?php foreach ($branches as $b): ?>
    <a href="dashboard.php<?= buildQuery(['branch'=>$b], ['status']) ?>" class="nav-item <?= ($filterBranch===$b) ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <?= $b ?>
    </a>
    <?php endforeach; ?>

    <span class="nav-section-label" style="margin-top:1.5rem;">Paramètres</span>
    <a href="#quotas-section" class="nav-item" onclick="document.getElementById('quotas-section').scrollIntoView({behavior:'smooth'});return false;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
      Quotas & Responsables
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-info">
      <div class="admin-avatar">A</div>
      <div>
        <div class="admin-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
        <div class="admin-role">Administrateur</div>
      </div>
    </div>
    <a href="../auth/logout.php" class="logout-btn" title="Déconnexion">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
  </div>
</aside>

<!-- MAIN -->
<main class="main">

  <!-- TOP BAR -->
  <header class="topbar">
    <button class="hamburger" id="menuToggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
    <div class="topbar-title">
      <?php if ($filterBranch): ?>
        Branche <span class="branch-tag"><?= htmlspecialchars($filterBranch) ?></span>
      <?php elseif ($filterStatus === 'pending'): ?>
        Demandes en attente
      <?php elseif ($filterStatus === 'accepted'): ?>
        Stagaires acceptés
      <?php elseif ($filterStatus === 'rejected'): ?>
        Demandes rejetées
      <?php else: ?>
        Vue générale
      <?php endif; ?>
    </div>
    <form class="search-form" method="get" action="dashboard.php">
      <?php if ($filterBranch): ?><input type="hidden" name="branch" value="<?= htmlspecialchars($filterBranch) ?>"><?php endif; ?>
      <?php if ($filterStatus): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="search" name="q" placeholder="Rechercher…" value="<?= htmlspecialchars($filterSearch) ?>"/>
    </form>
  </header>

  <div class="content">

    <!-- FLASH -->
    <?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type'] ?>" id="flashMsg">
      <?php if ($flash['type']==='success'): ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      <?php endif; ?>
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- KPI CARDS -->
    <div class="kpi-grid">
      <div class="kpi-card kpi-total">
        <div class="kpi-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-num"><?= number_format($stats['total']) ?></div>
          <div class="kpi-label">Total Demandes</div>
        </div>
      </div>
      <div class="kpi-card kpi-pending">
        <div class="kpi-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-num"><?= number_format($stats['pending']) ?></div>
          <div class="kpi-label">En attente</div>
        </div>
      </div>
      <div class="kpi-card kpi-accepted">
        <div class="kpi-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-num"><?= number_format($stats['accepted']) ?></div>
          <div class="kpi-label">Acceptés</div>
        </div>
      </div>
      <div class="kpi-card kpi-rejected">
        <div class="kpi-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-num"><?= number_format($stats['rejected']) ?></div>
          <div class="kpi-label">Rejetés</div>
        </div>
      </div>
    </div>

    <!-- BRANCH OVERVIEW BARS (only on main view) -->
    <?php if (!$filterBranch && !$filterStatus && !$filterSearch): ?>
    <section class="branch-overview">
      <div class="section-header">
        <h2>Aperçu par branche</h2>
      </div>
      <div class="branch-bars">
        <?php
        // build a map
        $bmap = [];
        foreach ($branchSummary as $br) $bmap[$br['branch']] = $br;
        $qmap = [];
        foreach ($allQuotas as $q) $qmap[$q['branch']] = $q;
        foreach ($branches as $b):
            $br = $bmap[$b] ?? ['total'=>0,'accepted'=>0,'pending'=>0,'rejected'=>0];
            $q  = $qmap[$b] ?? ['max_quota'=>10];
            $pct = $q['max_quota'] > 0 ? min(100, round($br['accepted'] / $q['max_quota'] * 100)) : 0;
            $over = ($br['accepted'] > $q['max_quota']);
        ?>
        <a href="dashboard.php<?= buildQuery(['branch'=>$b], ['status','q','page']) ?>" class="branch-bar-item">
          <div class="branch-bar-header">
            <span class="branch-code"><?= $b ?></span>
            <span class="branch-counts">
              <span class="dot dot-pending"></span><?= $br['pending'] ?>
              <span class="dot dot-accepted" style="margin-left:.5rem;"></span><?= $br['accepted'] ?>/<?= $q['max_quota'] ?>
            </span>
          </div>
          <div class="progress-track">
            <div class="progress-fill <?= $over ? 'over' : '' ?>" style="width:<?= $pct ?>%"></div>
          </div>
          <div class="branch-bar-foot">
            <span><?= $pct ?>% du quota</span>
            <span><?= $br['total'] ?> total</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- STAGAIRES TABLE -->
    <section class="table-section">
      <div class="section-header">
        <h2>
          Stagaires
          <?php if ($filterBranch): ?><span class="branch-tag sm"><?= htmlspecialchars($filterBranch) ?></span><?php endif; ?>
        </h2>
        <span class="result-count"><?= $total ?> résultat<?= $total!==1?'s':'' ?></span>
      </div>

      <?php if (empty($stagaires)): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13,2 13,9 20,9"/></svg>
        <p>Aucun stagaire trouvé.</p>
      </div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nom complet</th>
              <th>Branche</th>
              <th>Université</th>
              <th>Email / Tél</th>
              <th>Date</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($stagaires as $s): ?>
            <tr class="row-<?= $s['status'] ?>">
              <td class="mono"><?= $s['id'] ?></td>
              <td>
                <div class="name-cell">
                  <div class="avatar-circle"><?= mb_strtoupper(mb_substr($s['first_name'],0,1)) . mb_strtoupper(mb_substr($s['last_name'],0,1)) ?></div>
                  <div>
                    <div class="full-name"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></div>
                    <div class="speciality-text"><?= htmlspecialchars($s['speciality']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="branch-pill"><?= htmlspecialchars($s['branch']) ?></span></td>
              <td class="university"><?= htmlspecialchars($s['university']) ?></td>
              <td>
                <div class="contact-cell">
                  <span><?= htmlspecialchars($s['email']) ?></span>
                  <span class="phone"><?= htmlspecialchars($s['phone']) ?></span>
                </div>
              </td>
              <td class="mono date-col"><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
              <td>
                <?php
                $st = $s['status'];
                $labels = ['pending'=>'En attente','accepted'=>'Accepté','rejected'=>'Rejeté'];
                ?>
                <span class="status-badge status-<?= $st ?>"><?= $labels[$st] ?></span>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn-action btn-view" onclick="openDetail(<?= $s['id'] ?>)" title="Voir détails">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <?php if ($s['status'] !== 'accepted'): ?>
                  <form method="post" style="display:inline" onsubmit="return confirm('Accepter ce stagaire ?')">
                    <input type="hidden" name="action" value="set_status"/>
                    <input type="hidden" name="id" value="<?= $s['id'] ?>"/>
                    <input type="hidden" name="status" value="accepted"/>
                    <button class="btn-action btn-accept" title="Accepter">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                    </button>
                  </form>
                  <?php endif; ?>
                  <?php if ($s['status'] !== 'rejected'): ?>
                  <button class="btn-action btn-reject" title="Rejeter" onclick="openReject(<?= $s['id'] ?>)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="dashboard.php<?= buildQuery(['page'=>$page-1]) ?>" class="page-btn">‹</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
          <a href="dashboard.php<?= buildQuery(['page'=>$i]) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="dashboard.php<?= buildQuery(['page'=>$page+1]) ?>" class="page-btn">›</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </section>

    <!-- QUOTAS & BRANCH MANAGERS -->
    <section class="quotas-section" id="quotas-section">
      <div class="section-header">
        <h2>Quotas & Responsables de branches</h2>
      </div>
      <div class="quotas-grid">
        <?php
        $qmap2 = [];
        foreach ($allQuotas as $q) $qmap2[$q['branch']] = $q;
        foreach ($branches as $b):
            $q = $qmap2[$b] ?? ['branch'=>$b,'max_quota'=>10,'manager_email'=>'','manager_name'=>''];
            $acc = $bmap[$b]['accepted'] ?? 0;
            $pct = $q['max_quota'] > 0 ? min(100, round($acc / $q['max_quota'] * 100)) : 0;
        ?>
        <div class="quota-card">
          <div class="quota-card-head">
            <span class="branch-code"><?= $b ?></span>
            <span class="quota-usage <?= ($acc >= $q['max_quota']) ? 'quota-full' : '' ?>"><?= $acc ?>/<?= $q['max_quota'] ?></span>
          </div>
          <div class="quota-bar-wrap">
            <div class="quota-bar" style="width:<?= $pct ?>%"></div>
          </div>
          <form method="post" class="quota-form">
            <input type="hidden" name="action" value="update_quota"/>
            <input type="hidden" name="branch" value="<?= $b ?>"/>
            <div class="quota-field">
              <label>Quota max</label>
              <input type="number" name="max_quota" min="0" max="999" value="<?= (int)$q['max_quota'] ?>" required/>
            </div>
            <div class="quota-field">
              <label>Nom responsable</label>
              <input type="text" name="manager_name" placeholder="Nom du responsable" value="<?= htmlspecialchars($q['manager_name'] ?? '') ?>"/>
            </div>
            <div class="quota-field">
              <label>Email responsable</label>
              <input type="email" name="manager_email" placeholder="responsable@sonatrach.dz" value="<?= htmlspecialchars($q['manager_email'] ?? '') ?>"/>
            </div>
            <button type="submit" class="quota-save-btn">Enregistrer</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

  </div><!-- /content -->
</main>

<!-- DETAIL MODAL -->
<div class="modal-overlay" id="detailOverlay" onclick="closeModals()">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="closeModals()">✕</button>
    <div id="modalContent">Chargement…</div>
  </div>
</div>

<!-- REJECT MODAL -->
<div class="modal-overlay" id="rejectOverlay" onclick="closeModals()">
  <div class="modal modal-sm" onclick="event.stopPropagation()">
    <button class="modal-close" onclick="closeModals()">✕</button>
    <h3 style="margin-bottom:1rem;">Rejeter la demande</h3>
    <form method="post" id="rejectForm">
      <input type="hidden" name="action" value="set_status"/>
      <input type="hidden" name="status" value="rejected"/>
      <input type="hidden" name="id" id="rejectId"/>
      <div class="quota-field" style="margin-bottom:1rem;">
        <label>Motif (optionnel)</label>
        <textarea name="notes" rows="4" placeholder="Raison du rejet…" style="width:100%;background:var(--bg-field);border:1px solid var(--border);color:var(--text-primary);border-radius:var(--radius);padding:.75rem 1rem;font-family:inherit;resize:vertical;"></textarea>
      </div>
      <button type="submit" class="quota-save-btn" style="background:#c0392b;box-shadow:0 4px 16px rgba(192,57,43,.3);">Confirmer le rejet</button>
    </form>
  </div>
</div>

<!-- DATA for JS modal -->
<script>
const STAGAIRES = <?php
$map = [];
foreach ($stagaires as $s) {
    $map[$s['id']] = [
        'id'           => $s['id'],
        'name'         => $s['first_name'] . ' ' . $s['last_name'],
        'branch'       => $s['branch'],
        'university'   => $s['university'],
        'speciality'   => $s['speciality'],
        'email'        => $s['email'],
        'phone'        => $s['phone'],
        'status'       => $s['status'],
        'notes'        => $s['admin_notes'] ?? '',
        'created_at'   => $s['created_at'],
        'id_card'      => $s['national_id_card_path'] ?? '',
        'photo'        => $s['self_photo_path'] ?? '',
        'birth'        => $s['birth_certificate_path'] ?? '',
    ];
}
echo json_encode($map);
?>;

function openDetail(id) {
  const s = STAGAIRES[id];
  if (!s) return;
  const statusMap = {pending:'En attente', accepted:'Accepté', rejected:'Rejeté'};
  const docLink = (path, label) => path
    ? `<a href="../${path}" target="_blank" class="doc-link">${label} ↗</a>`
    : `<span style="color:var(--text-muted)">Non fourni</span>`;

  document.getElementById('modalContent').innerHTML = `
    <div class="detail-header">
      <div class="detail-avatar">${s.name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase()}</div>
      <div>
        <h2>${s.name}</h2>
        <div class="detail-meta">${s.branch} · ${s.university}</div>
        <span class="status-badge status-${s.status}">${statusMap[s.status]}</span>
      </div>
    </div>
    <div class="detail-grid">
      <div class="detail-field"><span>Spécialité</span><strong>${s.speciality}</strong></div>
      <div class="detail-field"><span>Email</span><strong>${s.email}</strong></div>
      <div class="detail-field"><span>Téléphone</span><strong>${s.phone}</strong></div>
      <div class="detail-field"><span>Date</span><strong>${new Date(s.created_at).toLocaleDateString('fr-DZ')}</strong></div>
      ${s.notes ? `<div class="detail-field full"><span>Notes</span><strong>${s.notes}</strong></div>` : ''}
    </div>
    <div class="detail-docs">
      <div class="doc-item">${docLink(s.id_card,'Carte d\'identité')}</div>
      <div class="doc-item">${docLink(s.photo,'Photo personnelle')}</div>
      <div class="doc-item">${docLink(s.birth,'Acte de naissance')}</div>
    </div>
    <div class="detail-actions">
      ${s.status !== 'accepted' ? `<form method="post" onsubmit="return confirm('Accepter ?')"><input type="hidden" name="action" value="set_status"/><input type="hidden" name="id" value="${id}"/><input type="hidden" name="status" value="accepted"/><button class="quota-save-btn">✓ Accepter</button></form>` : ''}
      ${s.status !== 'rejected' ? `<button class="quota-save-btn" style="background:#c0392b;box-shadow:0 4px 16px rgba(192,57,43,.3);" onclick="closeModals();openReject(${id})">✕ Rejeter</button>` : ''}
    </div>
  `;
  document.getElementById('detailOverlay').classList.add('open');
}

function openReject(id) {
  document.getElementById('rejectId').value = id;
  document.getElementById('rejectOverlay').classList.add('open');
}

function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });

// Hamburger
document.getElementById('menuToggle').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
});

// Auto-hide flash
const fl = document.getElementById('flashMsg');
if (fl) setTimeout(() => fl.style.opacity = '0', 3000);
</script>
</body>
</html>