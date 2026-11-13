<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Virtual Tour — Govirtual</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/cms-components.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/cms-components.css')) ?>">
    <link rel="stylesheet" href="assets/css/smart-hub.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/smart-hub.css')) ?>">
    <link rel="stylesheet" href="assets/css/ai-hub.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/ai-hub.css')) ?>">
    <link rel="stylesheet" href="assets/css/cms-redesign.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/cms-redesign.css')) ?>">
    <link rel="stylesheet" href="assets/css/cms-refine.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/cms-refine.css')) ?>">
    <script src="assets/vendor/lucide.min.js" defer></script>
    <script defer>document.addEventListener('DOMContentLoaded',()=>window.lucide?.createIcons());</script>
</head>
<body class="cms-app-body">
<div class="cms-app-frame">
    <aside class="cms-navigation" id="cmsNavigation" aria-label="Navigasi utama">
        <div class="cms-brand"><span class="cms-brand-mark" aria-hidden="true">G</span><div><strong>Govirtual</strong><small>Tour Manager</small></div><button type="button" id="closeCmsNavigation" aria-label="Tutup menu"><i data-lucide="x" aria-hidden="true"></i></button></div>
        <nav class="main-tabs">
            <?php if ($cmsRuntimeReady): ?><a class="<?= $activeTab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard"><span aria-hidden="true"><i data-lucide="layout-dashboard"></i></span>Dashboard</a><?php endif; ?>
            <span class="cms-nav-label">Workspace</span>
            <a class="<?= $activeTab === 'smart' ? 'active' : '' ?>" href="?tab=smart"><span aria-hidden="true"><i data-lucide="blocks"></i></span>Smart Hub</a>
            <a class="<?= $activeTab === 'aihub' ? 'active' : '' ?>" href="?tab=aihub"><span aria-hidden="true"><i data-lucide="bot"></i></span>AI Guide Hub</a>
            <?php if ($cmsRuntimeReady): ?><a class="<?= in_array($activeTab, ['lokasi', 'voice', 'video', 'optimizer', 'audit', 'object', 'config', 'skin'], true) ? 'active' : '' ?>" href="?tab=lokasi&lokasi=<?= e($selectedId) ?>"><span aria-hidden="true"><i data-lucide="settings-2"></i></span>Configuration</a><?php endif; ?>
            <span class="cms-nav-label">Media &amp; Tools</span>
            <?php if ($cmsRuntimeReady): ?><a class="<?= in_array($activeTab, ['languages', 'translations'], true) ? 'active' : '' ?>" href="?tab=languages"><span aria-hidden="true"><i data-lucide="languages"></i></span>Bahasa &amp; Terjemahan</a><?php endif; ?>
            <?php if ($cmsRuntimeReady): ?><a class="<?= $activeTab === 'video360' ? 'active' : '' ?>" href="?tab=video360"><span aria-hidden="true"><i data-lucide="circle-play"></i></span>Video 360</a><?php endif; ?>
            <a class="<?= $activeTab === 'loading' ? 'active' : '' ?>" href="?tab=loading"><span aria-hidden="true"><i data-lucide="gauge"></i></span>Optimasi Loading</a>
            <?php if ($cmsRuntimeReady): ?><a class="<?= $activeTab === 'custom' ? 'active' : '' ?>" href="?tab=custom"><span aria-hidden="true"><i data-lucide="code-2"></i></span>Custom CSS</a><?php endif; ?>
        </nav>
        <?php $cmsMe = cmsCurrentUser(); ?><div class="cms-userbox"><span><i data-lucide="circle-user-round" aria-hidden="true"></i> <?= e($cmsMe['name'] ?? $cmsMe['username'] ?? '') ?></span><small><?= e(CMS_ROLES[$cmsMe['role'] ?? ''] ?? '') ?></small><a href="users.php"><?= cmsIsAdmin() ? 'Pengguna' : 'Akun saya' ?></a><a href="logout.php">Keluar</a></div><div class="cms-navigation-footer"><small>Project aktif</small><strong><?= e($activeProject['name']) ?></strong><span><?= e($projectRoot) ?></span><a href="projects.php"><i data-lucide="repeat-2" aria-hidden="true"></i> Ganti project</a></div>
    </aside>
    <div class="cms-navigation-scrim" id="cmsNavigationScrim" hidden></div>
    <main class="shell">
    <?php $tabLabels = ['dashboard'=>'Dashboard','config'=>'Konfigurasi Global','languages'=>'Manajemen Bahasa','translations'=>'Translation Center','lokasi'=>'Data Lokasi','voice'=>'Voice Over','video'=>'Video','optimizer'=>'Media Optimizer','audit'=>'Audit','object'=>'Object Informasi','video360'=>'Video 360','custom'=>'Custom CSS','skin'=>'Template Skin','smart'=>'Smart Hub','aihub'=>'AI Guide Hub','loading'=>'Optimasi Loading']; $pageLabel = $tabLabels[$activeTab] ?? 'Data Lokasi'; ?>
    <header class="topbar">
        <button type="button" class="cms-menu-button" id="openCmsNavigation" aria-controls="cmsNavigation" aria-expanded="false" aria-label="Buka menu"><i data-lucide="menu" aria-hidden="true"></i></button>
        <div class="cms-page-heading"><nav class="cms-breadcrumbs" aria-label="Breadcrumb"><a href="?tab=dashboard">CMS Virtual Tour</a><span>/</span><span aria-current="page"><?= e($pageLabel) ?></span></nav><h1><?= e($pageLabel) ?></h1><small><?= e($activeProject['name']) ?></small></div>
        <div class="topbar-actions"><?php if ($skinTemplateId !== ''): ?><a class="preview" href="<?= e(projectUrl($skinTemplateId . '/')) ?>" target="_blank" rel="noopener"><span aria-hidden="true"><i data-lucide="external-link"></i></span>Pratinjau Tur</a><?php endif; ?><a class="cms-create-action" href="?tab=lokasi&lokasi=baru"><span aria-hidden="true"><i data-lucide="plus"></i></span>Buat Baru</a></div>
    </header>

    <?php if ($message !== ''): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <?php if (!in_array($activeTab, ['dashboard', 'languages', 'translations', 'custom', 'video360', 'smart', 'aihub', 'loading'], true)): ?>
    <div class="layout">
        <aside class="sidebar card">
            <div class="sidebar-title"><div><h2>Lokasi</h2><small><?= count($detectedTours) ?> tur terdeteksi</small></div><a href="?tab=lokasi&lokasi=baru" class="add">＋ Baru</a></div>
            <div class="sidebar-tools">
                <input type="search" id="locationSearch" placeholder="Cari lokasi…" aria-label="Cari lokasi">
            </div>
            <nav class="sidebar-location-list" id="sidebarLocationList">
                <?php foreach ($sidebarLocations as $id => $item): $isUnsaved = isset($detectedTours[$id]) && !isset($database[$id]); $tourStatus = $sidebarTourStatus[$id] ?? null; $isComplete = $tourStatus !== null && $tourStatus['score'] === 100; $locationName = $item['bahasa']['id']['lokasi'] ?? humanizeLocationId($id); ?>
                    <a class="location <?= $id === $selectedId ? 'active' : '' ?> <?= $isUnsaved ? 'unsaved' : '' ?> <?= $isComplete ? 'complete' : '' ?>" data-location-category="<?= e(tourCategory($id)) ?>" data-location-search="<?= e(strtolower($locationName . ' ' . $id)) ?>" href="?tab=<?= e($activeTab) ?>&lokasi=<?= e($id) ?>">
                        <span class="location-title"><strong><?= e($locationName) ?></strong><?php if ($isUnsaved): ?><i>Belum disimpan</i><?php elseif ($isComplete): ?><i class="complete-badge" title="Semua komponen lengkap">✓ Lengkap</i><?php elseif ($tourStatus !== null): ?><i class="progress-badge"><?= e($tourStatus['score']) ?>%</i><?php endif; ?></span>
                        <small><?= e($id) ?><?= isset($detectedTours[$id]) ? ' · ' . e($detectedTours[$id]['path']) : '' ?></small>
                    </a>
                <?php endforeach; ?>
                <div class="sidebar-empty" id="sidebarLocationEmpty" hidden>Tidak ada lokasi yang cocok.</div>
            </nav>
        </aside>
        <script src="assets/js/cms-shell.js"></script>
        <div class="location-workspace">
            <div class="location-toolbar">
            <nav class="location-tabs">
                <a class="<?= $activeTab === 'lokasi' ? 'active' : '' ?>" href="?tab=lokasi&lokasi=<?= e($selectedId) ?>">Data</a>
                <a class="<?= $activeTab === 'voice' ? 'active' : '' ?> <?= isset($database[$selectedId]) ? '' : 'disabled' ?>" href="?tab=voice&lokasi=<?= e($selectedId) ?>">Media · Voice Over</a>
                <a class="<?= $activeTab === 'video' ? 'active' : '' ?> <?= isset($detectedTours[$selectedId]) ? '' : 'disabled' ?>" href="?tab=video&lokasi=<?= e($selectedId) ?>">Media · Video</a>
                <a class="<?= $activeTab === 'optimizer' ? 'active' : '' ?> <?= isset($detectedTours[$selectedId]) ? '' : 'disabled' ?>" href="?tab=optimizer&lokasi=<?= e($selectedId) ?>">Tools · Optimizer</a>
                <a class="<?= $activeTab === 'audit' ? 'active' : '' ?> <?= isset($detectedTours[$selectedId]) ? '' : 'disabled' ?>" href="?tab=audit&lokasi=<?= e($selectedId) ?>">Tools · Audit</a>
                <a class="<?= $activeTab === 'object' ? 'active' : '' ?> <?= isset($database[$selectedId]) ? '' : 'disabled' ?>" href="?tab=object&lokasi=<?= e($selectedId) ?>">Object Informasi</a>
                <a class="<?= $activeTab === 'config' ? 'active' : '' ?>" href="?tab=config&lokasi=<?= e($selectedId) ?>">Konfigurasi Global</a>
                <a class="<?= $activeTab === 'skin' ? 'active' : '' ?>" href="?tab=skin&lokasi=<?= e($selectedId) ?>">Template Skin</a>
            </nav>
            <?php if ($selectedId !== 'baru' && isset($detectedTours[$selectedId])): ?><a class="location-local-tour" href="<?= e(projectUrl(trim($detectedTours[$selectedId]['path'], '/') . '/')) ?>" target="_blank" rel="noopener">▶ Kunjungi URL Lokal</a><?php endif; ?>
            </div>
    <?php endif; ?>

    <?php if (in_array($activeTab, ['languages', 'translations'], true)): ?>
        <nav class="language-section-tabs" aria-label="Menu bahasa dan terjemahan">
            <a class="<?= $activeTab === 'languages' ? 'active' : '' ?>" href="?tab=languages">Manajemen Bahasa</a>
            <a class="<?= $activeTab === 'translations' ? 'active' : '' ?>" href="?tab=translations">Translation Center</a>
        </nav>
    <?php endif; ?>

    <?php if ($activeTab === 'dashboard'): ?>
        <?php $completeTours = count(array_filter($dashboardTours, static fn(array $tour): bool => $tour['data_complete'])); $builtTours = count(array_filter($dashboardTours, static fn(array $tour): bool => $tour['bundle_built'])); $implementedTours = count(array_filter($dashboardTours, static fn(array $tour): bool => $tour['implemented'])); ?>
        <section class="dashboard" aria-labelledby="dashboardTitle">
            <div class="dashboard-intro"><div><span class="eyebrow">OVERVIEW</span><h2 id="dashboardTitle">Dashboard Produksi</h2><p>Pantau kesiapan konten dan kelola seluruh lokasi virtual tour dari satu tempat.</p></div><a href="?tab=lokasi&lokasi=baru"><span aria-hidden="true">＋</span> Buat Lokasi Baru</a></div>
            <div class="dashboard-summary">
                <article class="card"><i aria-hidden="true"><span data-lucide="map-pin"></span></i><div><span>Total lokasi</span><strong><?= count($dashboardTours) ?></strong><small>Folder tur terdeteksi</small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="circle-check"></span></i><div><span>Data lengkap</span><strong><?= $completeTours ?></strong><small>Profil Smart Kit lengkap</small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="package-check"></span></i><div><span>Bundle dibuat</span><strong><?= $builtTours ?></strong><small>Paket scene tersedia</small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="rocket"></span></i><div><span>Sudah implementasi</span><strong><?= $implementedTours ?></strong><small>Smart Kit aktif di tur</small></div></article>
            </div>
            <section class="card dashboard-list">
                <div class="dashboard-heading"><div><h2>Daftar Produksi Virtual Tour</h2><p>Status dan kategori bersumber dari paket Smart Kit setiap lokasi.</p></div><div class="dashboard-controls"><label><span>Cari lokasi</span><input type="search" id="dashboardSearch" placeholder="Nama, ID, atau kategori…"></label><label><span>Status data</span><select id="dashboardFilter"><option value="all">Semua status</option><option value="incomplete">Belum lengkap</option><option value="complete">Data lengkap</option></select></label></div></div>
                <?php $dashboardCategories = []; foreach ($dashboardTours as $item) { $dashboardCategories[$item['category']] = $item['category_label']; } asort($dashboardCategories, SORT_NATURAL | SORT_FLAG_CASE); ?>
                <nav class="dashboard-category-tabs" aria-label="Kategori lokasi Smart Kit">
                    <button type="button" class="active" data-dashboard-category="all" aria-pressed="true">Semua <span><?= count($dashboardTours) ?></span></button>
                    <?php foreach ($dashboardCategories as $category => $label): $categoryCount = count(array_filter($dashboardTours, static fn(array $item): bool => $item['category'] === $category)); ?>
                        <button type="button" data-dashboard-category="<?= e($category) ?>" aria-pressed="false"><?= e($label) ?> <span><?= $categoryCount ?></span></button>
                    <?php endforeach; ?>
                </nav>
                <div class="bulk-toolbar"><label><input type="checkbox" id="bulkSelectVisible"> Pilih yang tampil</label><strong id="bulkSelectedCount">0 lokasi dipilih</strong><div><button type="button" class="bulk-production-button" id="runBulkProduction">▶ Bulk Production</button><button type="button" id="openBulkEdit">✎ Bulk Edit</button><button type="button" data-bulk-action="translate">✦ Auto Translate</button><button type="button" data-bulk-action="voice">♫ Generate Voice</button><button type="button" data-bulk-action="health">♡ Health Check</button><button type="button" class="audit-all" id="openAuditAll">Audit Semua</button></div></div>
                <div class="bulk-progress" id="bulkProgress" hidden></div>
                <div class="bulk-production-results" id="bulkProductionResults" hidden aria-live="polite"></div>
                <div class="dashboard-table-wrap"><table class="dashboard-table"><thead><tr><th class="select-column"></th><th>Lokasi</th><th>Kategori Smart Kit</th><th>Kelengkapan data</th><th>Bundle</th><th>Implementasi</th><th>URL Local</th><th>URL Deploy</th><th class="dashboard-actions-column">Aksi</th></tr></thead><tbody id="tourStatusList">
                    <?php foreach ($dashboardTours as $tour): ?><tr class="tour-status-card" data-location-id="<?= e($tour['id']) ?>" data-dashboard-category-row="<?= e($tour['category']) ?>" data-dashboard-name="<?= e($tour['search_text']) ?>" data-dashboard-score="<?= $tour['score'] ?>">
                        <td class="select-column"><input type="checkbox" class="bulk-location" value="<?= e($tour['id']) ?>" aria-label="Pilih <?= e($tour['name']) ?>"></td><td><strong><?= e($tour['name']) ?></strong><small><?= e($tour['id']) ?></small></td><td><span class="smartkit-category"><?= e($tour['category_label']) ?></span></td><td><span class="table-progress"><i style="--progress:<?= $tour['score'] ?>%"></i><b><?= $tour['score'] ?>%</b></span></td>
                        <td><span class="readiness-indicator <?= $tour['bundle_built'] ? 'is-ready' : 'is-pending' ?>" title="<?= $tour['bundle_built'] ? 'Bundle Smart Kit sudah dibuat' : 'Bundle Smart Kit belum dibuat' ?>"><i data-lucide="<?= $tour['bundle_built'] ? 'check' : 'minus' ?>" aria-hidden="true"></i></span></td><td><span class="readiness-indicator <?= $tour['implemented'] ? 'is-ready' : 'is-pending' ?>" title="<?= $tour['implemented'] ? 'Smart Kit sudah diterapkan' : 'Smart Kit belum diterapkan' ?>"><i data-lucide="<?= $tour['implemented'] ? 'check' : 'minus' ?>" aria-hidden="true"></i></span></td>
                        <td><div class="dashboard-preview-links"><button type="button" data-tour-preview="<?= e($tour['local_url']) ?>" data-preview-title="<?= e($tour['name']) ?> · Local">Preview</button></div></td><td><div class="dashboard-preview-links"><button type="button" data-tour-preview="<?= e($tour['deploy_url']) ?>" data-preview-title="<?= e($tour['name']) ?> · Deploy">Preview</button></div></td><td class="dashboard-actions-column"><div class="dashboard-row-actions"><button type="button" data-open-location="<?= e($tour['path']) ?>" title="Buka folder di File Explorer"><i data-lucide="folder-open" aria-hidden="true"></i></button><button type="button" data-zip-location="<?= e($tour['id']) ?>" data-zip-path="<?= e($tour['path']) ?>" data-zip-score="<?= e($tour['score']) ?>" title="ZIP semua isi lokasi"><span>ZIP</span></button><a class="dashboard-manage" href="?tab=lokasi&lokasi=<?= e($tour['id']) ?>">Kelola</a></div></td>
                    </tr><?php endforeach; ?>
                    <tr id="dashboardEmpty" hidden><td colspan="9" class="dashboard-empty">Tidak ada lokasi pada kategori atau filter ini.</td></tr>
                </tbody></table></div>
            </section>
            <div class="tour-preview-modal" id="tourPreviewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="tourPreviewTitle">
                <div class="tour-preview-backdrop" data-close-tour-preview></div>
                <div class="tour-preview-dialog" id="tourPreviewDialog">
                    <div class="tour-preview-heading"><div><strong id="tourPreviewTitle">Preview Virtual Tour</strong><small id="tourPreviewDevice">Laptop HD · viewport 1366 × 768 px</small></div><div class="tour-preview-heading-actions"><div class="tour-device-switch" role="group" aria-label="Ukuran preview"><button type="button" data-tour-device="desktop-fhd">Desktop FHD</button><button type="button" class="active" data-tour-device="laptop-hd">Laptop HD</button><button type="button" data-tour-device="desktop-small">Desktop 1280</button><button type="button" data-tour-device="iphone">iPhone</button><button type="button" data-tour-device="android">Android</button><button type="button" data-tour-device="mobile-small">HP Kecil</button></div><a id="tourPreviewExternal" href="#" target="_blank" rel="noopener">Buka tab baru ↗</a><button type="button" class="tour-preview-close" data-close-tour-preview aria-label="Tutup preview">×</button></div></div>
                    <div class="tour-preview-canvas" id="tourPreviewCanvas"><div class="tour-device-mockup" id="tourDeviceMockup"><iframe id="tourPreviewFrame" title="Preview Virtual Tour" allow="fullscreen; autoplay" allowfullscreen></iframe></div></div>
                </div>
            </div>
            <div class="bulk-edit-modal" id="bulkEditModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="bulkEditTitle">
                <div class="bulk-edit-backdrop" data-close-bulk-edit></div><div class="bulk-edit-dialog"><header><div><strong id="bulkEditTitle">Bulk Edit Lokasi</strong><small id="bulkEditSelection">0 lokasi dipilih</small></div><button type="button" data-close-bulk-edit aria-label="Tutup">×</button></header>
                    <p class="bulk-edit-notice">Setiap field default-nya <b>Jangan ubah</b>. Nilai lama dan seluruh data bahasa tetap dipertahankan.</p>
                    <div class="bulk-edit-fields">
                        <input type="hidden" id="bulkCategoryMode" value="keep"><input type="hidden" id="bulkCategoryValue" value="uncategorized">
                        <label><span>Status has_drone</span><select id="bulkDroneStatusMode"><option value="keep">Jangan ubah</option><option value="set_true">Aktifkan</option><option value="set_false">Nonaktifkan</option></select></label>
                        <label><span>Node drone</span><select id="bulkDroneNodeMode"><option value="keep">Jangan ubah</option><option value="set">Ganti node menjadi…</option><option value="clear">Kosongkan node secara eksplisit</option></select><input id="bulkDroneNodeValue" maxlength="200" placeholder="Node ID / Custom Node ID" disabled><small>Node hanya dapat dikosongkan melalui pilihan “Kosongkan” agar tidak terhapus tanpa sengaja.</small></label>
                    </div>
                    <div class="bulk-edit-error" id="bulkEditError" hidden></div><div class="bulk-edit-results" id="bulkEditResults" hidden></div>
                    <footer><button type="button" data-close-bulk-edit>Batal</button><button type="button" class="save" id="applyBulkEdit">Terapkan perubahan</button></footer>
                </div>
            </div>
            <div class="audit-all-modal" id="auditAllModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="auditAllTitle">
                <div class="audit-all-backdrop" data-close-audit-all></div><div class="audit-all-dialog"><header><div><strong id="auditAllTitle">Audit Semua Lokasi</strong><small>Ringkasan komponen yang masih perlu dilengkapi</small></div><button type="button" data-close-audit-all>×</button></header><div class="audit-all-controls"><input type="search" id="auditAllSearch" placeholder="Cari lokasi atau masalah…"><select id="auditAllFilter"><option value="all">Semua lokasi</option><option value="incomplete">Hanya bermasalah</option><option value="complete">Sudah lengkap</option></select></div><div class="audit-all-list" id="auditAllList"></div></div>
            </div>
            <div class="asset-detail-modal" id="assetDetailModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="assetDetailTitle">
                <div class="asset-detail-backdrop" data-close-asset-detail></div>
                <div class="asset-detail-dialog">
                    <div class="asset-detail-heading"><div><strong id="assetDetailTitle">Ukuran Aset</strong><small id="assetDetailSubtitle"></small></div><button type="button" data-close-asset-detail aria-label="Tutup detail aset">×</button></div>
                    <div class="asset-total-card"><span>Total ukuran tour</span><strong id="assetDetailTotal">0 MB</strong></div>
                    <div class="asset-detail-content"><h3>Aset yang disarankan untuk dioptimasi</h3><div id="assetDetailList"></div></div>
                </div>
            </div>
            <div class="health-detail-modal" id="healthDetailModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="healthDetailTitle">
                <div class="health-detail-backdrop" data-close-health-detail></div>
                <div class="health-detail-dialog">
                    <div class="health-detail-heading"><div><strong id="healthDetailTitle">Health Check Deploy</strong><small id="healthDetailSubtitle"></small></div><button type="button" data-close-health-detail aria-label="Tutup hasil health check">×</button></div>
                    <div id="healthDetailSummary" class="health-detail-summary"></div>
                    <div id="healthDetailList" class="health-detail-list"></div>
                </div>
            </div>
            <script>
            (() => {
                const root = document.currentScript.closest('.dashboard');
                const search = root.querySelector('#dashboardSearch');
                const status = root.querySelector('#dashboardFilter');
                const tabs = [...root.querySelectorAll('[data-dashboard-category]')];
                const rows = [...root.querySelectorAll('.tour-status-card')];
                const empty = root.querySelector('#dashboardEmpty');
                let category = 'all';
                const apply = () => {
                    const query = search.value.trim().toLocaleLowerCase('id');
                    let visible = 0;
                    rows.forEach(row => {
                        const score = Number(row.dataset.dashboardScore || 0);
                        const matchesCategory = category === 'all' || row.dataset.dashboardCategoryRow === category;
                        const matchesStatus = status.value === 'all' || (status.value === 'complete' ? score === 100 : score < 100);
                        const matchesSearch = !query || (row.dataset.dashboardName || '').includes(query);
                        const show = matchesCategory && matchesStatus && matchesSearch;
                        row.hidden = !show;
                        row.style.display = show ? '' : 'none';
                        if (show) visible++;
                    });
                    empty.hidden = visible > 0;
                    empty.style.display = visible > 0 ? 'none' : '';
                };
                tabs.forEach(tab => tab.addEventListener('click', () => {
                    category = tab.dataset.dashboardCategory;
                    tabs.forEach(item => {
                        const active = item === tab;
                        item.classList.toggle('active', active);
                        item.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                    apply();
                }));
                search.addEventListener('input', apply);
                status.addEventListener('change', apply);
            })();
            </script>
        </section>
    <?php elseif ($activeTab === 'translations'): ?>
        <section class="translation-center" id="translationCenter">
            <section class="card section translation-center-hero"><div class="section-heading"><div><span class="step">TR</span><div><h2>Translation Center</h2><p>Kelola data CMS, seluruh pano.xml, dan data terjemahan generated skin.js.</p></div></div><div class="translation-summary"><strong id="translationCompletion">…</strong><span id="translationCount">Memuat data</span></div></div>
                <nav class="translation-language-tabs" id="translationLanguageTabs"><?php foreach ($languageRegistry as $code => $language): ?><button type="button" data-language="<?= e($code) ?>" class="<?= $code === array_key_first($languageRegistry) ? 'active' : '' ?>"><span><?= e($language['flag'] ?? '🌐') ?></span><?= e($language['label']) ?><small data-language-badge>…</small></button><?php endforeach; ?></nav>
            </section>
            <section class="card translation-center-body">
                <div class="translation-toolbar"><input type="search" id="translationSearch" placeholder="Cari lokasi, teks sumber, atau terjemahan…"><select id="translationLocation"><option value="all">Semua lokasi</option><?php foreach ($sidebarLocations as $id => $location): ?><option value="<?= e($id) ?>"><?= e($location['bahasa']['id']['lokasi'] ?? humanizeLocationId($id)) ?></option><?php endforeach; ?></select><select id="translationSource"><option value="all">Semua sumber</option><option value="database">CMS location data</option><option value="pano">pano.xml</option><option value="skin">skin.js generated</option></select><label class="translation-missing-toggle"><input type="checkbox" id="translationMissingOnly"> Hanya yang kosong</label></div>
                <div class="translation-source-counts" id="translationSourceCounts"></div>
                <div class="translation-list-head"><label><input type="checkbox" id="translationSelectAll"> Pilih yang tampil</label><span id="translationVisibleCount"></span></div>
                <div class="translation-center-list" id="translationCenterList"><div class="translation-center-loading">Memindai seluruh data terjemahan…</div></div>
                <div class="translation-pagination"><button type="button" id="translationPrevious">← Sebelumnya</button><span id="translationPage"></span><button type="button" id="translationNext">Berikutnya →</button></div>
            </section>
            <div class="translation-save-bar"><span id="translationProgress">Centang field yang ingin disimpan.</span><button type="button" class="save" id="saveTranslations">Simpan Terpilih</button></div>
        </section>
    <?php elseif ($activeTab === 'lokasi'): ?>
        <form method="post" class="editor">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
            <input type="hidden" name="original_id" value="<?= e($selectedId === 'baru' ? '' : $selectedId) ?>">

            <section class="card section">
                <div class="section-heading"><div><span class="step">01</span><h2>Data lokasi</h2></div><p>Disimpan ke global-database.js</p></div>
                <div class="grid two">
                    <label><span>ID lokasi</span><input required name="id_lokasi" pattern="[A-Za-z0-9_-]+" value="<?= e($selected['id_lokasi']) ?>" placeholder="contoh: monumennasional"></label>
                    <label><span class="drone-field-heading"><span>Drone</span><span class="toggle-control"><input type="checkbox" name="has_drone" value="1" aria-label="Aktifkan fitur drone" <?= !empty($selected['has_drone']) ? 'checked' : '' ?>><i aria-hidden="true"></i></span></span><span class="drone-control"><input id="droneInput" name="drone" value="<?= e($selected['drone']) ?>" placeholder="Node ID / Custom Node ID"><button type="button" id="openPanoramaPicker" onclick="const modal=document.getElementById('panoramaModal');if(modal){modal.classList.add('open');modal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');}">Pilih Panorama &amp; View</button></span><input type="hidden" id="dronePan" name="drone_pan" value="<?= e($selected['drone_pan'] ?? 0) ?>"><input type="hidden" id="droneTilt" name="drone_tilt" value="<?= e($selected['drone_tilt'] ?? 0) ?>"><input type="hidden" id="droneFov" name="drone_fov" value="<?= e($selected['drone_fov'] ?? 70) ?>"><small id="droneViewSummary">Target view: pan <?= e($selected['drone_pan'] ?? 0) ?>° · tilt <?= e($selected['drone_tilt'] ?? 0) ?>° · FoV <?= e($selected['drone_fov'] ?? 70) ?>°</small></label>
                </div>
                <?php if ($selectedPanoramas !== []): ?>
                    <div class="panorama-modal" id="panoramaModal" aria-hidden="true">
                      <div class="panorama-backdrop" data-close-panorama></div>
                      <div class="panorama-dialog" role="dialog" aria-modal="true" aria-labelledby="panoramaTitle">
                        <div class="panorama-heading"><div><strong id="panoramaTitle">Pilih panorama drone</strong><small>Custom node ID diprioritaskan; jika kosong akan memakai node ID.</small></div><div class="panorama-actions"><span><?= count($selectedPanoramas) ?> panorama</span><button type="button" data-close-panorama aria-label="Tutup">×</button></div></div>
                        <div class="panorama-grid" id="panoramaGrid">
                            <?php foreach ($selectedPanoramas as $panorama): $isSelected = (string) $selected['drone'] === $panorama['value']; ?>
                                <button type="button" class="panorama-option <?= $isSelected ? 'selected' : '' ?>" data-node-value="<?= e($panorama['value']) ?>">
                                    <span class="panorama-image">
                                        <?php if ($panorama['thumbnail'] !== ''): ?><img src="<?= e($panorama['thumbnail']) ?>" alt="<?= e($panorama['nodeId']) ?>" loading="lazy"><?php else: ?><em>Tanpa thumbnail</em><?php endif; ?>
                                    </span>
                                    <span class="panorama-info">
                                        <strong><?= e($panorama['title'] !== '' ? $panorama['title'] : $panorama['nodeId']) ?></strong>
                                        <small>Node: <?= e($panorama['nodeId']) ?></small>
                                        <?php if ($panorama['customNodeId'] !== ''): ?><small class="custom-id">Custom: <?= e($panorama['customNodeId']) ?></small><?php endif; ?>
                                    </span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="drone-view-picker" id="droneViewPicker" hidden>
                            <div class="drone-view-stage"><iframe id="droneViewFrame" title="Atur target view panorama drone" allow="fullscreen"></iframe><span class="drone-crosshair" aria-hidden="true"><i></i><b></b></span><div class="drone-view-loading" id="droneViewLoading">Memuat panorama…</div></div>
                            <div class="drone-view-toolbar"><button type="button" class="translate" id="droneViewBack">← Ganti panorama</button><span id="droneLiveView">Pan 0° · Tilt 0° · FoV 70°</span><button type="button" class="save" id="saveDroneView">Gunakan Arah Ini</button></div>
                        </div>
                      </div>
                    </div>
                <?php endif; ?>
            </section>

            <section class="card section">
                <div class="section-heading"><div><span class="step">02</span><h2>Bahasa</h2></div><button type="button" class="translate" id="autoTranslate">✦ Auto Translate dari Indonesia</button></div>
                <div class="tabs" role="tablist">
                    <?php $first = true; foreach (cmsLanguages() as $code => $label): ?>
                        <button type="button" class="tab <?= $first ? 'active' : '' ?>" data-tab="<?= e($code) ?>"><?= e($label) ?></button>
                    <?php $first = false; endforeach; ?>
                </div>
                <?php $first = true; foreach (cmsLanguages() as $code => $label): $lang = $selected['bahasa'][$code] ?? []; ?>
                    <div class="language-panel <?= $first ? 'active' : '' ?>" data-panel="<?= e($code) ?>">
                        <label><span>Nama lokasi — <?= e($label) ?></span><input name="lokasi[<?= e($code) ?>]" value="<?= e($lang['lokasi'] ?? '') ?>"></label>
                        <label><span>Welcome text — <?= e($label) ?></span><textarea name="welcomeText[<?= e($code) ?>]" rows="3"><?= e($lang['welcomeText'] ?? '') ?></textarea></label>
                    </div>
                <?php $first = false; endforeach; ?>
            </section>

            <div class="actions">
                <?php if ($selectedId !== 'baru' && isset($database[$selectedId])): ?>
                    <button class="danger" name="action" value="delete" formnovalidate data-cute-confirm data-confirm-icon="🗺️" data-confirm-title="Hapus lokasi ini?" data-confirm-message="Data lokasi akan dihapus dari database CMS. Pastikan lokasinya memang sudah tidak digunakan." data-confirm-action="Ya, hapus lokasi">Hapus lokasi</button>
                <?php endif; ?>
                <a class="ah-btn ah-btn--ghost" href="?tab=aihub" title="Sinkronkan nama &amp; teks sambutan ke AI Guide Hub setelah disimpan">Hubungkan ke AI Guide Hub</a>
                <button class="save" name="action" value="save_location">Simpan data lokasi</button>
            </div>
        </form>
    <?php elseif ($activeTab === 'languages'): ?>
        <section class="language-manager">
            <div class="language-manager-controls card section">
              <section class="language-registry-section">
                <div class="section-heading"><div><span class="step">LG</span><h2>Manajemen Bahasa</h2></div><span class="language-count"><?= count($languageRegistry) ?> bahasa aktif</span></div>
                <div class="language-registry-list">
                    <?php foreach ($languageRegistry as $code => $language): ?>
                        <article class="language-registry-item"><span class="language-flag" aria-hidden="true"><?= e($language['flag'] ?? '🌐') ?></span><div><strong><?= e($language['label']) ?></strong><small><?= e($code) ?> · Pano <?= e($language['pano']) ?> · Audio <?= e($language['audio']) ?>.mp3</small></div><div class="language-card-actions"><span class="language-direction"><?= strtoupper(e($language['direction'])) ?></span><?php if ($code !== 'id'): ?><form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>"><input type="hidden" name="action" value="remove_language"><input type="hidden" name="language_code" value="<?= e($code) ?>"><button type="submit" class="language-remove" title="Hapus <?= e($language['label']) ?>" data-cute-confirm data-confirm-icon="<?= e($language['flag'] ?? '🌐') ?>" data-confirm-title="Yakin mau dihapus?" data-confirm-message="Bahasa <?= e($language['label']) ?> akan dihapus dari semua lokasi dan virtual tour. Backup tetap dibuat otomatis." data-confirm-action="Ya, hapus">Hapus</button></form><?php else: ?><span class="language-required">Sumber</span><?php endif; ?></div></article>
                    <?php endforeach; ?>
                </div>
              </section>
              <form method="post" class="language-add-form" id="languageAddForm">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
                <input type="hidden" name="action" value="add_language">
                <div class="section-heading"><div><span class="step">＋</span><h2>Tambah Bahasa</h2></div></div>
                <label class="language-preset-field"><span>Pilih bahasa yang ingin ditambahkan</span><select id="languagePreset" required>
                    <option value="">— Pilih bahasa —</option>
                    <?php foreach ([
                        ['ko','Korean','한국어','ko-KR','ko','ko','KR','ltr','🇰🇷'], ['fr','French','Français','fr-FR','fr','fr','FR','ltr','🇫🇷'], ['de','German','Deutsch','de-DE','de','de','DE','ltr','🇩🇪'],
                        ['es','Spanish','Español','es-ES','es','es','ES','ltr','🇪🇸'], ['pt','Portuguese','Português','pt-BR','pt','pt','PT','ltr','🇧🇷'], ['it','Italian','Italiano','it-IT','it','it','IT','ltr','🇮🇹'],
                        ['nl','Dutch','Nederlands','nl-NL','nl','nl','NL','ltr','🇳🇱'], ['ru','Russian','Русский','ru-RU','ru','ru','RU','ltr','🇷🇺'], ['hi','Hindi','हिन्दी','hi-IN','hi','hi','HI','ltr','🇮🇳'],
                        ['th','Thai','ไทย','th-TH','th','th','TH','ltr','🇹🇭'], ['vi','Vietnamese','Tiếng Việt','vi-VN','vi','vi','VI','ltr','🇻🇳'], ['tr','Turkish','Türkçe','tr-TR','tr','tr','TR','ltr','🇹🇷'],
                        ['ms','Malay','Bahasa Melayu','ms-MY','ms','ms','MY','ltr','🇲🇾'], ['he','Hebrew','עברית','he-IL','he','he','HE','ltr','🇮🇱'], ['fa','Persian','فارسی','fa-IR','fa','fa','FA','ltr','🇮🇷']
                    ] as $preset): if (isset($languageRegistry[$preset[0]])) continue; ?>
                        <option value="<?= e($preset[0]) ?>" data-language='<?= e(json_encode($preset, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'><?= e($preset[2]) ?> — <?= e($preset[1]) ?></option>
                    <?php endforeach; ?>
                </select></label>
                <input type="hidden" name="language_label"><input type="hidden" name="native_label"><input type="hidden" name="language_flag"><input type="hidden" name="language_code"><input type="hidden" name="pano_code"><input type="hidden" name="translate_code"><input type="hidden" name="webview_code"><input type="hidden" name="audio_code"><input type="hidden" name="direction" value="ltr">
                <div class="language-add-progress" id="languageAddProgress" hidden aria-live="polite">
                    <div><strong id="languageProgressTitle">Menyiapkan bahasa…</strong><span id="languageProgressPercent">0%</span></div>
                    <progress id="languageProgressBar" max="100" value="0"></progress>
                    <small id="languageProgressDetail">Jangan tutup halaman selama proses berlangsung.</small>
                </div>
                <div class="actions"><button class="save" type="submit">Tambah Bahasa</button></div>
              </form>
            </div>
            <section class="card section language-preview-card">
                <div class="section-heading"><div><span class="step">👁</span><div><h2>Preview Pemilih Bahasa</h2><p>Tampilan yang digunakan pada iframe Pano2VR.</p></div></div><a href="<?= e(projectUrl($assetsDirectoryName . '/' . ($runtimeDirectoryName === $assetsDirectoryName ? '' : $runtimeDirectoryName . '/') . 'bahasa.html')) ?>" target="_blank" rel="noopener">Buka penuh ↗</a></div>
                <div class="language-preview-frame"><iframe src="<?= e(projectUrl($assetsDirectoryName . '/' . ($runtimeDirectoryName === $assetsDirectoryName ? '' : $runtimeDirectoryName . '/') . 'bahasa.html')) ?>" title="Preview pemilih bahasa"></iframe></div>
            </section>
        </section>
    <?php elseif ($activeTab === 'config'): ?>
        <form method="post" class="editor global-editor">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
            <section class="config-hero card">
                <div class="config-hero-icon">🔗</div><div><span class="eyebrow">PUSAT TAUTAN</span><h2>Konfigurasi Global</h2><p>Satu alamat dasar untuk seluruh menu webview pada project aktif.</p></div>
                <span class="config-file-badge">assets/global-config.js</span>
            </section>
            <section class="card section config-base-section">
                <div class="section-heading"><div><span class="step">01</span><div><h2>Alamat Webview Utama</h2><p>Domain dan folder utama tempat halaman PHP berada.</p></div></div><span class="config-status-dot"><i></i> Global</span></div>
                <label class="config-base-field"><span>Base URL</span><div class="config-url-input"><span>🌐</span><input id="globalBaseUrl" type="url" required name="base_url" value="<?= e($config['base_url'] ?? '') ?>" placeholder="https://webview.example.id/jakarta"><button type="button" id="testGlobalBaseUrl">Buka ↗</button></div><small>Gunakan HTTPS dan jangan tambahkan garis miring di bagian akhir.</small></label>
            </section>
            <section class="card section config-links-section">
                <div class="section-heading"><div><span class="step">02</span><div><h2>Endpoint Menu</h2><p>Atur halaman dan parameter untuk setiap tombol pada skin.</p></div></div><div class="config-heading-actions"><span class="config-endpoint-count" id="configEndpointCount"><?= count($config['links'] ?? []) ?> endpoint</span><button type="button" class="translate" id="openEndpointCreator">＋ Tambah endpoint</button></div></div>
                <div class="config-link-grid" id="configLinkGrid">
                    <?php $linkMeta = ['link_menu'=>['Menu Utama','Navigasi utama webview'],'link_layanan'=>['Layanan','Daftar layanan lokasi'],'link_kategori'=>['Kategori','Kategori destinasi dan konten'],'link_informasi'=>['Informasi','Informasi detail lokasi'],'link_jelajah'=>['Jelajah','Konten eksplorasi destinasi']]; foreach (($config['links'] ?? []) as $name => $link): $defaultMeta = $linkMeta[$name] ?? [humanizeLocationId($name), 'Endpoint webview custom']; $meta = [$link['label'] ?? $defaultMeta[0], $defaultMeta[1]]; ?>
                        <article class="config-link-card" data-config-link data-config-name="<?= e($name) ?>">
                            <input type="hidden" name="links[<?= e($name) ?>][label]" value="<?= e($meta[0]) ?>">
                            <header><span class="config-link-icon">🔗</span><div><strong><?= e($meta[0]) ?></strong><small><?= e($meta[1]) ?></small></div><code><?= e($name) ?></code><div class="config-card-tools"><button type="button" class="config-link-edit" data-edit-config-link aria-label="Edit <?= e($meta[0]) ?>">✎</button><button type="button" class="config-link-delete" data-delete-config-link aria-label="Hapus <?= e($meta[0]) ?>">×</button></div></header>
                            <div class="config-link-fields"><label><span>Halaman</span><div><input required data-config-page name="links[<?= e($name) ?>][page]" value="<?= e($link['page'] ?? '') ?>"><small>.php</small></div></label><label><span>Parameter lokasi</span><div><small>?</small><input required data-config-param name="links[<?= e($name) ?>][param]" value="<?= e($link['param'] ?? '') ?>"><small>=id_lokasi</small></div></label></div>
                            <div class="config-link-preview"><span>Preview URL</span><code data-config-preview></code><div><button type="button" data-copy-config-url>Salin</button><button type="button" data-preview-config-url>Preview</button><a href="#" data-open-config-url target="_blank" rel="noopener">Buka ↗</a></div></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <div class="endpoint-preview-modal" id="endpointPreviewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="endpointPreviewTitle">
                <div class="endpoint-preview-backdrop" data-close-endpoint-preview></div>
                <div class="endpoint-preview-dialog">
                    <header><div><strong id="endpointPreviewTitle">Preview Endpoint</strong><small id="endpointPreviewUrl"></small></div><button type="button" data-close-endpoint-preview aria-label="Tutup preview">×</button></header>
                    <iframe id="endpointPreviewFrame" title="Preview Endpoint Menu"></iframe>
                </div>
            </div>
            <div class="config-save-bar"><span>Perubahan berlaku untuk seluruh virtual tour</span><button class="save" name="action" value="save_config">Simpan perubahan</button></div>
        </form>
    <?php elseif ($activeTab === 'loading'): ?>
        <?php require __DIR__ . '/optimizer.php'; ?>
    <?php elseif ($activeTab === 'smart'): ?>
        <?php if ($smartHubState !== null) require __DIR__ . '/smart.php'; ?>
    <?php elseif ($activeTab === 'aihub'): ?>
        <?php if ($aiHubState !== null) require __DIR__ . '/aihub.php'; ?>
    <?php elseif ($activeTab === 'custom'): ?>
        <form method="post" class="editor custom-css-editor">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
            <section class="card section">
                <div class="section-heading custom-css-heading">
                    <div><span class="step">CSS</span><div><h2>Custom CSS</h2><p>Edit stylesheet khusus tampilan Virtual Tour.</p></div></div>
                    <code>assets/custom.css</code>
                </div>
                <label class="code-editor-label">
                    <span>Isi stylesheet</span>
                    <textarea name="custom_css" class="code-editor" spellcheck="false" autocapitalize="off" autocomplete="off" aria-label="Isi assets/custom.css"><?= e($customCss) ?></textarea>
                </label>
            </section>
            <div class="actions"><button class="save" name="action" value="save_custom_css">Simpan Custom CSS</button></div>
        </form>
    <?php elseif ($activeTab === 'skin'): ?>
        <form method="post" class="editor skin-sync-editor">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
            <section class="card section">
                <div class="section-heading"><div><span class="step">SK</span><div><h2>Template Skin Semua Lokasi</h2><p>Gunakan skin.js Monumen Nasional sebagai acuan untuk seluruh folder output.</p></div></div><span class="skin-template-badge">Sumber: monumen-nasional/skin.js</span></div>
                <div class="skin-preview">
                    <div class="skin-preview-heading">
                        <div><strong>Preview Template Skin Jakarta</strong><small>Overlay modular assets/jkt</small></div>
                        <div class="skin-preview-actions"><button type="button" id="loadSkinPreview">Lihat preview</button><a href="<?= e(projectUrl($skinTemplateId . '/')) ?>" target="_blank" rel="noopener">Buka penuh ↗</a></div>
                    </div>
                    <p>Preview dibuka sebagai popup agar posisi halaman CMS tetap terkunci.</p>
                </div>
                <div class="skin-preview-modal" id="skinPreviewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="skinPreviewTitle">
                    <div class="skin-preview-backdrop" data-close-skin-preview></div>
                    <div class="skin-preview-dialog">
                        <div class="skin-preview-modal-heading"><div><strong id="skinPreviewTitle">Preview Template Skin Jakarta</strong><small>assets/jkt</small></div><button type="button" data-close-skin-preview aria-label="Tutup preview">×</button></div>
                        <iframe id="skinPreviewFrame" title="Preview Template Skin Jakarta" allow="fullscreen; autoplay" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="skin-notice"><strong>Satu klik untuk semua</strong><span>CMS mengganti <code>skin.js</code>, player, gambar skin, dan library pada seluruh lokasi selain Monumen Nasional. Setiap file lama dicadangkan lebih dulu ke <code>.skin-backups</code>. Panorama, pano.xml, tiles, video, dan data lokasi tetap dipertahankan.</span></div>
            </section>
            <div class="actions"><button class="save" name="action" value="sync_skin" data-cute-confirm data-confirm-icon="✨" data-confirm-title="Terapkan skin Monas ke semua lokasi?" data-confirm-message="Seluruh skin.js target akan diganti. File lama dicadangkan otomatis dan Monumen Nasional tidak diubah." data-confirm-action="Terapkan ke semua">Terapkan Skin ke Semua Lokasi</button></div>
        </form>
        <form method="post" id="projectTemplateForm"><input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>"><input type="hidden" name="project_action" value="set_template"></form>
    <?php elseif ($activeTab === 'object'): ?>
        <section class="card section object-editor">
            <div class="section-heading"><div><span class="step">OI</span><h2>Object Informasi</h2></div><div class="object-head-actions"><button type="button" class="translate" id="importWebElements">＋ Ambil WebElement</button><button type="button" class="translate" id="addObject">＋ Manual</button><button type="button" class="save object-save" id="saveObjects">Simpan</button></div></div>
            <div class="skin-notice"><strong>WebElement pano.xml</strong><span><?= count($selectedWebElements) ?> elemen terdeteksi. OI hanya dinilai jika lokasi memiliki WebElement. Data memakai <code>assets/oi-database.js</code> dan <code>assets/oi-config.js</code>.</span></div>
            <div class="object-layout"><aside class="object-list-panel"><input id="objectSearch" type="search" placeholder="Cari object…"><div id="objectCount"></div><div id="objectList"></div></aside><div id="objectForm" class="object-form"><div class="object-empty">Pilih atau tambahkan object informasi.</div></div></div>
            <div class="voice-progress" id="objectProgress">Data tersimpan ke assets/oi-database.js.</div>
        </section>
    <?php elseif ($activeTab === 'voice'): ?>
        <section class="card section voice-editor">
            <div class="section-heading"><div><span class="step">VO</span><h2>Generate Voice Over</h2></div><p>Hasil otomatis disimpan ke assets/audio/{id_lokasi}/</p></div>
            <select id="voiceLocation" hidden><option value="<?= e($selectedId) ?>" selected><?= e($selectedId) ?></option></select>
            <div id="voiceLanguages" class="voice-languages"></div>
            <div class="voice-progress" id="voiceProgress">Siap membuat audio.</div>
            <div class="actions"><button type="button" class="save" id="generateVoice">Generate Semua Audio</button></div>
        </section>
    <?php elseif ($activeTab === 'video'): ?>
        <section class="card section video-editor">
            <div class="section-heading"><div><span class="step">VD</span><h2>Video Mobile & Desktop</h2></div><p>Upload baru akan menimpa video lama.</p></div>
            <select id="videoLocation" hidden><option value="<?= e($selectedId) ?>" data-path="<?= e($detectedTours[$selectedId]['path'] ?? '') ?>" selected><?= e($selectedId) ?></option></select>
            <div class="video-grid">
                <article class="video-card">
                    <div class="video-card-head"><div><strong>Mobile</strong><small>video/mobile.webm</small></div><span id="mobileStatus" class="media-status">Memeriksa…</span></div>
                    <video id="mobilePreview" controls playsinline></video>
                    <label class="upload-box"><span>Pilih mobile.webm</span><input type="file" id="mobileFile" accept="video/webm,.webm"></label>
                </article>
                <article class="video-card">
                    <div class="video-card-head"><div><strong>Desktop</strong><small>video/desktop.webm</small></div><span id="desktopStatus" class="media-status">Memeriksa…</span></div>
                    <video id="desktopPreview" controls playsinline></video>
                    <label class="upload-box"><span>Pilih desktop.webm</span><input type="file" id="desktopFile" accept="video/webm,.webm"></label>
                </article>
            </div>
            <div class="voice-progress" id="videoProgress">Pilih satu atau dua video WebM untuk diunggah.</div>
            <div class="actions"><button type="button" class="save" id="uploadVideo">Upload & Timpa Video</button></div>
        </section>
    <?php elseif ($activeTab === 'video360'): ?>
        <section class="card section video360-editor">
            <div class="section-heading"><div><span class="step">360</span><div><h2>Folder Video 360</h2><p>Setiap item merupakan folder tersendiri di assets/360/videos.</p></div></div><div class="config-heading-actions"><span><?= count($video360Items) ?> folder</span><button type="button" class="translate" id="launchVideo360Encoder">⚙ Buka Encoder GUI</button></div></div>
            <form id="video360UploadForm" class="video360-upload-form">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
                <label><span>Nama Folder / ID Video</span><input name="video_id" id="video360Id" required pattern="[a-z0-9](?:[a-z0-9_-]{0,62}[a-z0-9])?" placeholder="contoh: kota-tua"></label>
                <label class="upload-box"><span>Upload Video Sumber</span><small>MP4, WebM, MOV, atau MKV. File masuk ke folder ID di atas.</small><input type="file" name="files[]" id="video360Files" accept="video/*,.mp4,.webm,.mov,.mkv" required></label>
                <div class="voice-progress" id="video360Progress">Tambahkan folder baru dengan nama ID dan satu video sumber. Gunakan Encoder GUI untuk membuat hasil HLS.</div>
                <div class="actions"><button type="submit" class="save">＋ Tambah Folder Video</button></div>
            </form>
            <div class="video360-list">
                <?php foreach ($video360Items as $videoId => $item): ?>
                <article class="card video360-item" data-video360-id="<?= e($videoId) ?>">
                    <div><strong><?= e($videoId) ?></strong><small>assets/360/videos/<?= e($videoId) ?>/</small></div>
                    <span class="media-status <?= $item['ready'] ? 'ready' : 'pending' ?>"><?= $item['ready'] ? 'Siap diputar' : 'Video sumber · siap encode' ?></span>
                    <div class="actions"><button type="button" class="translate" data-video360-preview="<?= e($videoId) ?>" <?= $item['ready'] ? '' : 'disabled' ?>>▶ Preview</button><button type="button" data-video360-copy="<?= e($item['code']) ?>">⧉ Salin JS</button><button type="button" data-video360-edit="<?= e($videoId) ?>">✎ Edit ID</button><button type="button" class="danger" data-video360-delete="<?= e($videoId) ?>">Hapus</button></div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif ($activeTab === 'optimizer'): ?>
        <section class="card section optimizer-editor">
            <div class="section-heading"><div><span class="step">OP</span><div><h2>Media Optimizer</h2><p>Konversi JPG/PNG menjadi WebP, resize, kompres, dan backup otomatis.</p></div></div><button type="button" class="translate" id="scanOptimizer">↻ Scan Ulang</button></div>
            <div class="optimizer-notice"><strong>Aman untuk aset tur</strong><span>Folder tiles dan library tidak diproses. File asli disimpan ke <code>.media-backups</code>, kemudian referensi pada XML, JS, HTML, JSON, dan CSS diperbarui otomatis.</span></div>
            <div class="optimizer-controls"><label><span>Lebar maksimum</span><select id="optimizerMaxWidth"><option value="1280">1280 px</option><option value="1920" selected>1920 px</option><option value="2560">2560 px</option><option value="4096">4096 px</option></select></label><label><span>Kualitas WebP</span><select id="optimizerQuality"><option value="70">70 — kecil</option><option value="82" selected>82 — rekomendasi</option><option value="90">90 — tinggi</option></select></label><div><strong id="optimizerSummary">Memindai gambar…</strong><small>Gambar rekomendasi akan dipilih otomatis.</small></div></div>
            <div class="optimizer-list-head"><label><input type="checkbox" id="optimizerSelectAll"> Pilih semua</label><span id="optimizerSelected">0 gambar dipilih</span></div>
            <div class="optimizer-list" id="optimizerList"><div class="optimizer-loading">Memindai aset gambar…</div></div>
            <div class="voice-progress" id="optimizerProgress">Siap memindai dan mengoptimasi gambar.</div>
            <div class="actions"><button type="button" class="save" id="runOptimizer">Optimasi Gambar Terpilih</button></div>
        </section>
    <?php else: ?>
        <section class="card section audit-editor">
            <div class="section-heading"><div><span class="step">AU</span><h2>Audit pano.xml</h2></div><p>Memeriksa link kosong dan teks yang belum diterjemahkan.</p></div>
            <?php if ($auditResult !== null): $stats = $auditResult['stats']; ?>
                <div class="audit-summary"><div><strong><?= e($stats['panoramas']) ?></strong><span>Panorama</span></div><div><strong><?= e($stats['hotspots']) ?></strong><span>Hotspot</span></div><div class="audit-error"><strong><?= e($stats['errors']) ?></strong><span>Link belum diisi</span></div><div class="audit-warning"><strong><?= e($stats['warnings']) ?></strong><span>Terjemahan kosong</span></div></div>
                <div class="pano-editor-actions"><strong>Editor pano.xml</strong><span>Field yang belum lengkap langsung ditandai pada editor.</span><button type="button" class="save" id="savePanoEditor">Simpan pano.xml</button></div>
                <nav class="pano-editor-tabs"><button type="button" class="active" data-pano-editor-tab="hotspot">Hotspot <span><?= count($auditResult['hotspots']) ?></span></button><button type="button" data-pano-editor-tab="translation">Terjemahan <span><?= count($auditResult['translations']) ?></span></button></nav>
                <?php $missingHotspotCount = count(array_filter($auditResult['hotspots'], static fn(array $hotspot): bool => trim((string) ($hotspot['url'] ?? '')) === '')); ?>
                <section class="pano-editor-panel active" data-pano-editor-panel="hotspot"><div class="pano-filter"><button type="button" class="active" data-hotspot-filter="all">Semua Hotspot</button><button type="button" data-hotspot-filter="missing">Belum Diisi (<?= e($missingHotspotCount) ?>)</button></div><div class="pano-editor-grid" id="panoHotspotEditor">
                    <?php foreach ($auditResult['hotspots'] as $hotspot): $isEst = stripos($hotspot['title'], 'est') !== false; ?>
                        <article class="pano-edit-card <?= $hotspot['url'] === '' ? 'needs-attention' : '' ?> <?= $isEst ? 'est-hotspot' : '' ?>" data-node-id="<?= e($hotspot['nodeId']) ?>" data-hotspot-id="<?= e($hotspot['id']) ?>"><header><div><strong><?= e($hotspot['title'] !== '' ? $hotspot['title'] : $hotspot['id']) ?></strong><small><?= e($hotspot['nodeId']) ?> · <?= e($hotspot['id']) ?> · <?= e($hotspot['skinId']) ?></small></div><?php if ($hotspot['url'] === ''): ?><button type="button" class="missing-info" title="URL hotspot belum diisi">! Link belum diisi</button><?php endif; ?></header><label><span>Title</span><input data-pano-field="title" value="<?= e($hotspot['title']) ?>"></label><label><span>URL / Node tujuan <?= $isEst ? '— pilih destinasi' : '' ?></span><?php if ($isEst): ?><select data-pano-field="url" data-est-url><option value="">— Pilih destinasi —</option><?php if ($hotspot['url'] !== '' && !in_array((string) parse_url($hotspot['url'], PHP_URL_PATH), array_map(static fn(string $slug): string => '/' . $slug, EST_DESTINATIONS), true)): ?><option value="<?= e($hotspot['url']) ?>" selected><?= e($hotspot['url']) ?></option><?php endif; ?><?php foreach (EST_DESTINATIONS as $slug): $url = 'https://jakarta.govirtual.id/' . $slug; ?><option value="<?= e($url) ?>" <?= $hotspot['url'] === $url ? 'selected' : '' ?>><?= e(mb_convert_case(str_replace('-', ' ', $slug), MB_CASE_TITLE, 'UTF-8')) ?></option><?php endforeach; ?></select><?php else: ?><input data-pano-field="url" value="<?= e($hotspot['url']) ?>"><?php endif; ?></label><label><span>Target</span><input data-pano-field="target" value="<?= e($hotspot['target']) ?>"></label></article>
                    <?php endforeach; ?>
                </div></section>
                <section class="pano-editor-panel" data-pano-editor-panel="translation"><div class="pano-filter"><button type="button" class="active" data-translation-filter="all">Semua Terjemahan</button><button type="button" data-translation-filter="missing">Belum Diisi (<?= e($stats['warnings']) ?>)</button></div><div class="translation-editor" id="panoTranslationEditor">
                    <?php foreach ($auditResult['translations'] as $translation): $translationMissing = (int) ($translation['missingCount'] ?? 0); ?><article class="translation-row <?= $translationMissing > 0 ? 'needs-translation' : '' ?>" data-source="<?= e($translation['source']) ?>"><div class="translation-source"><strong><?= e($translation['source']) ?></strong><button type="button" class="translation-auto" data-translate-pano title="Auto Translate Kosong" aria-label="Auto Translate Kosong">✦</button></div><?php if ($translationMissing > 0): ?><span class="translation-warning">! <?= $translationMissing ?> bahasa kosong</span><?php endif; ?><div class="translation-fields"><?php foreach ($auditResult['languages'] as $index => $language): $translationValue = (string) ($translation['values'][$index] ?? ''); $isDisplayedLanguage = in_array($index, $auditResult['displayLanguageIndexes'], true); $languageLabel = match (strtolower($language)) { 'en', 'en-us' => 'English', 'zh', 'zh-cn' => 'Mandarin', 'ar', 'ar-sa' => 'Arab', default => $language }; ?><?php if (!$isDisplayedLanguage): ?><textarea hidden data-translation-index="<?= $index ?>"><?= e($translationValue) ?></textarea><?php else: ?><label class="translation-field <?= trim($translationValue) === '' ? 'empty-translation' : '' ?>"><span><?= e($languageLabel) ?></span><textarea rows="1" data-translation-index="<?= $index ?>"><?= e($translationValue) ?></textarea></label><?php endif; ?><?php endforeach; ?></div></article><?php endforeach; ?>
                </div></section>
                <div class="voice-progress" id="panoEditorProgress">Perubahan belum disimpan.</div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
    <?php if (!in_array($activeTab, ['dashboard', 'languages', 'translations', 'custom', 'video360', 'smart', 'aihub', 'loading'], true)): ?>
        </div>
    </div>
    <?php endif; ?>
    </main>
</div>
<div class="category-create-modal" id="categoryCreateModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="categoryCreateTitle">
    <div class="category-create-backdrop" data-close-category-create></div>
    <div class="category-create-dialog"><span class="category-create-icon">✦</span><h2 id="categoryCreateTitle">Tambah kategori baru</h2><p>Kategori hanya akan tersedia pada project aktif.</p><label><span>Nama kategori</span><input id="newLocationCategory" placeholder="Contoh: Wisata Alam" maxlength="60"></label><div class="category-create-error" id="categoryCreateError" hidden></div><div class="category-create-actions"><button type="button" data-close-category-create>Batal</button><button type="button" class="save" id="saveLocationCategory">Tambah kategori</button></div></div>
</div>
<div class="unsaved-modal" id="unsavedModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="unsavedModalTitle">
    <div class="unsaved-modal-backdrop" data-unsaved-stay></div>
    <div class="unsaved-modal-dialog">
        <span class="unsaved-modal-icon">!</span>
        <div><h2 id="unsavedModalTitle">Perubahan belum disimpan</h2><p>Perubahan yang sudah dibuat akan hilang jika Anda meninggalkan halaman ini.</p></div>
        <div class="unsaved-modal-actions"><button type="button" data-unsaved-stay>Tetap di halaman</button><button type="button" class="leave" data-unsaved-leave>Tinggalkan halaman</button></div>
    </div>
</div>
<div class="endpoint-modal" id="endpointCreatorModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="endpointCreatorTitle">
    <div class="endpoint-modal-backdrop" data-close-endpoint-creator></div>
    <div class="endpoint-modal-dialog">
        <header><div><span>＋</span><div><h2 id="endpointCreatorTitle">Tambah Endpoint</h2><p>Buat koneksi webview baru tanpa mengedit file konfigurasi.</p></div></div><button type="button" data-close-endpoint-creator>×</button></header>
        <div class="endpoint-creator-grid">
            <label><span>Nama tampilan</span><input id="newEndpointLabel" placeholder="Contoh: Tiket Online"></label>
            <label><span>Nama variabel</span><input id="newEndpointName" placeholder="link_tiket"></label>
            <label><span>Halaman PHP</span><input id="newEndpointPage" placeholder="tiket"></label>
            <label><span>Parameter lokasi</span><input id="newEndpointParam" placeholder="lokasi"></label>
        </div>
        <div class="endpoint-modal-error" id="endpointCreatorError" hidden></div>
        <div class="endpoint-modal-actions"><button type="button" data-close-endpoint-creator>Batal</button><button type="button" class="save" id="createEndpoint">Tambah endpoint</button></div>
    </div>
</div>
<div class="cute-confirm-modal" id="cuteConfirmModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="cuteConfirmTitle">
    <div class="cute-confirm-backdrop" data-cute-confirm-cancel></div>
    <div class="cute-confirm-dialog">
        <span class="cute-confirm-spark one">✦</span><span class="cute-confirm-spark two">✧</span>
        <div class="cute-confirm-icon" id="cuteConfirmIcon">✨</div>
        <h2 id="cuteConfirmTitle">Lanjutkan proses?</h2>
        <p id="cuteConfirmMessage">Pastikan pilihanmu sudah benar sebelum melanjutkan.</p>
        <div class="cute-confirm-actions"><button type="button" data-cute-confirm-cancel>Gajadi deh</button><button type="button" class="confirm" id="cuteConfirmAccept">Ya, lanjutkan</button></div>
    </div>
</div>
<script src="custom-css-editor.js"></script>
<script>
const cmsNavigation=document.getElementById('cmsNavigation'),cmsNavigationScrim=document.getElementById('cmsNavigationScrim'),cmsNavigationToggle=document.getElementById('openCmsNavigation');
const setCmsNavigation=open=>{if(!cmsNavigation)return;cmsNavigation.classList.toggle('open',open);cmsNavigationScrim?.classList.toggle('open',open);if(cmsNavigationScrim)cmsNavigationScrim.hidden=!open;cmsNavigationToggle?.setAttribute('aria-expanded',open?'true':'false');document.body.classList.toggle('modal-open',open&&matchMedia('(max-width:1024px)').matches);};
cmsNavigationToggle?.addEventListener('click',()=>setCmsNavigation(true));document.getElementById('closeCmsNavigation')?.addEventListener('click',()=>setCmsNavigation(false));cmsNavigationScrim?.addEventListener('click',()=>setCmsNavigation(false));
const escapeHtml = value => String(value).replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));
const translationCenter = document.getElementById('translationCenter');
if (translationCenter) {
    const list=document.getElementById('translationCenterList'), search=document.getElementById('translationSearch'), locationFilter=document.getElementById('translationLocation'), sourceFilter=document.getElementById('translationSource'), missingOnly=document.getElementById('translationMissingOnly'), selectAll=document.getElementById('translationSelectAll'), progress=document.getElementById('translationProgress');
    let language=document.querySelector('[data-language].active').dataset.language,page=1,pages=1,requestId=0,timer;
    const loadTranslations=async()=>{const current=++requestId;list.innerHTML='<div class="translation-center-loading">Memindai data terjemahan…</div>';const params=new URLSearchParams({language,source:sourceFilter.value,location:locationFilter.value,search:search.value.trim(),missing:missingOnly.checked?'1':'0',page:String(page)});try{const response=await fetch('translation-center.php?'+params);const result=await response.json();if(current!==requestId)return;if(!response.ok||!result.success)throw new Error(result.message||'Data gagal dimuat.');pages=result.pages;document.getElementById('translationCompletion').textContent=(result.stats.total?Math.round(result.stats.completed/result.stats.total*100):100)+'%';document.getElementById('translationCount').textContent=result.stats.completed+' / '+result.stats.total+' field terisi';document.getElementById('translationVisibleCount').textContent=result.stats.filtered+' hasil';document.getElementById('translationSourceCounts').innerHTML='<span>CMS <b>'+result.stats.sourceCounts.database+'</b></span><span>pano.xml <b>'+result.stats.sourceCounts.pano+'</b></span><span>skin.js <b>'+result.stats.sourceCounts.skin+'</b></span>';document.getElementById('translationPage').textContent='Halaman '+page+' / '+pages;document.getElementById('translationPrevious').disabled=page<=1;document.getElementById('translationNext').disabled=page>=pages;selectAll.checked=false;list.innerHTML=result.rows.length?result.rows.map((row,index)=>'<article class="translation-center-row '+(!row.value.trim()?'missing':'')+'" data-index="'+index+'"><input type="checkbox" data-select-translation><div class="translation-row-meta"><span class="translation-source-badge '+row.sourceType+'">'+escapeHtml(row.label)+'</span><strong>'+escapeHtml(row.locationLabel)+'</strong><small>'+escapeHtml(row.location)+'</small></div><label><span>Sumber (Indonesia)</span><textarea readonly rows="2">'+escapeHtml(row.source)+'</textarea></label><label><span>Terjemahan</span><textarea rows="2" data-translation-value></textarea></label></article>').join(''):'<div class="translation-center-loading">Tidak ada data yang cocok.</div>';result.rows.forEach((row,index)=>{const card=list.querySelector('[data-index="'+index+'"]');card.dataset.row=JSON.stringify({sourceType:row.sourceType,location:row.location,key:row.key});card.querySelector('[data-translation-value]').value=row.value;});}catch(error){list.innerHTML='<div class="translation-center-loading error">'+escapeHtml(error.message)+'</div>';}};
    const reload=()=>{page=1;loadTranslations();}; search.addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(reload,250);});[locationFilter,sourceFilter,missingOnly].forEach(control=>control.addEventListener('change',reload));document.querySelectorAll('[data-language]').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('[data-language]').forEach(item=>item.classList.remove('active'));button.classList.add('active');language=button.dataset.language;reload();}));
    selectAll.addEventListener('change',()=>list.querySelectorAll('[data-select-translation]').forEach(input=>{input.checked=selectAll.checked;}));document.getElementById('translationPrevious').addEventListener('click',()=>{if(page>1){page--;loadTranslations();}});document.getElementById('translationNext').addEventListener('click',()=>{if(page<pages){page++;loadTranslations();}});
    document.getElementById('saveTranslations').addEventListener('click',async function(){const selected=Array.from(list.querySelectorAll('[data-select-translation]:checked')).map(input=>input.closest('.translation-center-row'));if(!selected.length){progress.textContent='Pilih minimal satu field untuk disimpan.';return;}const changes=selected.map(row=>({...JSON.parse(row.dataset.row),value:row.querySelector('[data-translation-value]').value}));this.disabled=true;progress.textContent='Menyimpan '+changes.length+' perubahan dan membuat backup…';try{const response=await fetch('translation-center.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>,language,changes})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Gagal menyimpan.');progress.textContent=result.message;await loadTranslations();}catch(error){progress.textContent='Gagal: '+error.message;}finally{this.disabled=false;}});loadTranslations();
}
const globalBaseUrl = document.getElementById('globalBaseUrl');
if (globalBaseUrl) {
    const categoryFields = document.getElementById('projectCategoryFields');
    const refreshCategoryHint = () => { const hint=document.getElementById('categoryEmptyHint');if(hint)hint.hidden=categoryFields.children.length>0; };
    document.getElementById('addProjectCategory')?.addEventListener('click',()=>{const label=document.createElement('label');label.innerHTML='<input name="categories[]" placeholder="Nama kategori" required><button type="button" data-remove-category>×</button>';categoryFields.appendChild(label);label.querySelector('input').focus();refreshCategoryHint();});
    categoryFields?.addEventListener('click',event=>{if(event.target.closest('[data-remove-category]')){event.target.closest('label').remove();refreshCategoryHint();}});
    const configLinkGrid = document.getElementById('configLinkGrid');
    const endpointModal = document.getElementById('endpointCreatorModal');
    const endpointPreviewModal = document.getElementById('endpointPreviewModal');
    const endpointPreviewFrame = document.getElementById('endpointPreviewFrame');
    let editingConfigCard = null;
    const buildConfigUrl = card => {
        const base = globalBaseUrl.value.trim().replace(/\/$/, '');
        const page = card.querySelector('[data-config-page]').value.trim();
        const param = card.querySelector('[data-config-param]').value.trim();
        return base + '/' + page + '.php?' + param + '={id_lokasi}';
    };
    const refreshConfigUrls = () => configLinkGrid.querySelectorAll('[data-config-link]').forEach(card => {
        const url = buildConfigUrl(card); card.querySelector('[data-config-preview]').textContent = url; card.querySelector('[data-open-config-url]').href = url.replace('{id_lokasi}', 'monumen-nasional');
    });
    globalBaseUrl.addEventListener('input', refreshConfigUrls); refreshConfigUrls();
    configLinkGrid.addEventListener('input', event => { if (event.target.matches('[data-config-page],[data-config-param]')) refreshConfigUrls(); });
    configLinkGrid.addEventListener('click', async event => {
        const copyButton = event.target.closest('[data-copy-config-url]');
        if (copyButton) { const url=buildConfigUrl(copyButton.closest('[data-config-link]')); try { await navigator.clipboard.writeText(url);copyButton.textContent='Tersalin ✓'; } catch(error){copyButton.textContent='Gagal';} setTimeout(()=>copyButton.textContent='Salin',1400);return; }
        const previewButton = event.target.closest('[data-preview-config-url]');
        if (previewButton) { const card=previewButton.closest('[data-config-link]'),url=buildConfigUrl(card).replace('{id_lokasi}','monumen-nasional');document.getElementById('endpointPreviewTitle').textContent='Preview '+card.querySelector('header strong').textContent;document.getElementById('endpointPreviewUrl').textContent=url;endpointPreviewFrame.src=url;endpointPreviewModal.classList.add('open');endpointPreviewModal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');return; }
        const editButton = event.target.closest('[data-edit-config-link]');
        if (editButton) {
            editingConfigCard=editButton.closest('[data-config-link]');
            document.getElementById('newEndpointLabel').value=editingConfigCard.querySelector('input[name$="[label]"]').value;
            document.getElementById('newEndpointName').value=editingConfigCard.dataset.configName;
            document.getElementById('newEndpointPage').value=editingConfigCard.querySelector('[data-config-page]').value;
            document.getElementById('newEndpointParam').value=editingConfigCard.querySelector('[data-config-param]').value;
            document.getElementById('endpointCreatorTitle').textContent='Edit Endpoint';document.getElementById('createEndpoint').textContent='Simpan perubahan';
            endpointModal.classList.add('open');endpointModal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');setTimeout(()=>document.getElementById('newEndpointLabel').focus(),50);return;
        }
        const deleteButton = event.target.closest('[data-delete-config-link]');
        if (deleteButton && await cuteConfirm({icon:'🧹',title:'Hapus endpoint ini?',message:'Endpoint akan dihapus dari konfigurasi setelah perubahan disimpan.',action:'Ya, hapus endpoint'})) { deleteButton.closest('[data-config-link]').remove(); document.getElementById('configEndpointCount').textContent=configLinkGrid.children.length+' endpoint'; }
    });
    document.getElementById('testGlobalBaseUrl').addEventListener('click', () => { const url=globalBaseUrl.value.trim(); if(url) window.open(url,'_blank','noopener'); });
    const closeEndpointPreview = () => { endpointPreviewModal.classList.remove('open');endpointPreviewModal.setAttribute('aria-hidden','true');endpointPreviewFrame.src='about:blank';document.body.classList.remove('modal-open'); };
    document.querySelectorAll('[data-close-endpoint-preview]').forEach(button=>button.addEventListener('click',closeEndpointPreview));
    const closeEndpointCreator = () => { endpointModal.classList.remove('open');endpointModal.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');document.getElementById('endpointCreatorError').hidden=true;editingConfigCard=null;document.getElementById('endpointCreatorTitle').textContent='Tambah Endpoint';document.getElementById('createEndpoint').textContent='Tambah endpoint'; };
    document.getElementById('openEndpointCreator').addEventListener('click',()=>{editingConfigCard=null;['newEndpointLabel','newEndpointName','newEndpointPage','newEndpointParam'].forEach(id=>document.getElementById(id).value='');document.getElementById('endpointCreatorTitle').textContent='Tambah Endpoint';document.getElementById('createEndpoint').textContent='Tambah endpoint';endpointModal.classList.add('open');endpointModal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');setTimeout(()=>document.getElementById('newEndpointLabel').focus(),50);});
    document.querySelectorAll('[data-close-endpoint-creator]').forEach(button=>button.addEventListener('click',closeEndpointCreator));
    document.getElementById('createEndpoint').addEventListener('click',()=>{
        const label=document.getElementById('newEndpointLabel').value.trim(), name=document.getElementById('newEndpointName').value.trim().toLowerCase().replace(/[^a-z0-9_-]/g,''), page=document.getElementById('newEndpointPage').value.trim().toLowerCase().replace(/[^a-z0-9_-]/g,''), param=document.getElementById('newEndpointParam').value.trim().toLowerCase().replace(/[^a-z0-9_-]/g,''), error=document.getElementById('endpointCreatorError');
        if(!label||!name||!page||!param){error.textContent='Semua field selain ikon wajib diisi.';error.hidden=false;return;}
        const duplicate=configLinkGrid.querySelector('[name="links['+CSS.escape(name)+'][page]"]');if(duplicate&&duplicate.closest('[data-config-link]')!==editingConfigCard){error.textContent='Nama variabel sudah digunakan.';error.hidden=false;return;}
        if(editingConfigCard){
            editingConfigCard.dataset.configName=name;editingConfigCard.querySelector('header strong').textContent=label;editingConfigCard.querySelector('header code').textContent=name;
            editingConfigCard.querySelector('input[name$="[label]"]').name='links['+name+'][label]';editingConfigCard.querySelector('input[name$="[label]"]').value=label;
            editingConfigCard.querySelector('[data-config-page]').name='links['+name+'][page]';editingConfigCard.querySelector('[data-config-page]').value=page;
            editingConfigCard.querySelector('[data-config-param]').name='links['+name+'][param]';editingConfigCard.querySelector('[data-config-param]').value=param;
            const updatedCard=editingConfigCard;refreshConfigUrls();closeEndpointCreator();updatedCard.scrollIntoView({behavior:'smooth',block:'center'});return;
        }
        const card=document.createElement('article');card.className='config-link-card';card.dataset.configLink='';
        card.dataset.configName=name;card.innerHTML='<input type="hidden" name="links['+escapeHtml(name)+'][label]" value="'+escapeHtml(label)+'"><header><span class="config-link-icon">🔗</span><div><strong>'+escapeHtml(label)+'</strong><small>Endpoint webview custom</small></div><code>'+escapeHtml(name)+'</code><div class="config-card-tools"><button type="button" class="config-link-edit" data-edit-config-link aria-label="Edit '+escapeHtml(label)+'">✎</button><button type="button" class="config-link-delete" data-delete-config-link aria-label="Hapus '+escapeHtml(label)+'">×</button></div></header><div class="config-link-fields"><label><span>Halaman</span><div><input required data-config-page name="links['+escapeHtml(name)+'][page]" value="'+escapeHtml(page)+'"><small>.php</small></div></label><label><span>Parameter lokasi</span><div><small>?</small><input required data-config-param name="links['+escapeHtml(name)+'][param]" value="'+escapeHtml(param)+'"><small>=id_lokasi</small></div></label></div><div class="config-link-preview"><span>Preview URL</span><code data-config-preview></code><div><button type="button" data-copy-config-url>Salin</button><button type="button" data-preview-config-url>Preview</button><a href="#" data-open-config-url target="_blank" rel="noopener">Buka ↗</a></div></div>';
        configLinkGrid.appendChild(card);refreshConfigUrls();document.getElementById('configEndpointCount').textContent=configLinkGrid.children.length+' endpoint';
        ['newEndpointLabel','newEndpointName','newEndpointPage','newEndpointParam'].forEach(id=>document.getElementById(id).value='');closeEndpointCreator();card.scrollIntoView({behavior:'smooth',block:'center'});
    });
}
const cuteConfirmModal = document.getElementById('cuteConfirmModal');
let cuteConfirmResolve = null;
function cuteConfirm(options = {}) {
    document.getElementById('cuteConfirmIcon').textContent = options.icon || '✨';
    document.getElementById('cuteConfirmTitle').textContent = options.title || 'Lanjutkan proses?';
    document.getElementById('cuteConfirmMessage').textContent = options.message || 'Pastikan pilihanmu sudah benar sebelum melanjutkan.';
    document.getElementById('cuteConfirmAccept').textContent = options.action || 'Ya, lanjutkan';
    cuteConfirmModal.classList.add('open'); cuteConfirmModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('modal-open');
    return new Promise(resolve => { cuteConfirmResolve = resolve; });
}
function closeCuteConfirm(result = false) {
    cuteConfirmModal.classList.remove('open'); cuteConfirmModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('modal-open');
    const resolve = cuteConfirmResolve; cuteConfirmResolve = null; if (resolve) resolve(result);
}
document.querySelectorAll('[data-cute-confirm-cancel]').forEach(button => button.addEventListener('click', () => closeCuteConfirm(false)));
document.getElementById('cuteConfirmAccept')?.addEventListener('click', () => closeCuteConfirm(true));
document.addEventListener('keydown', event => { if (event.key !== 'Escape') return; if (cuteConfirmModal.classList.contains('open')) closeCuteConfirm(false); const endpointModal=document.getElementById('endpointCreatorModal'); if(endpointModal?.classList.contains('open')) endpointModal.querySelector('[data-close-endpoint-creator]').click(); const previewModal=document.getElementById('endpointPreviewModal'); if(previewModal?.classList.contains('open')) previewModal.querySelector('[data-close-endpoint-preview]').click(); });
document.querySelectorAll('[data-cute-confirm]').forEach(button => button.addEventListener('click', async event => {
    if (button.dataset.confirmed === '1') { delete button.dataset.confirmed; return; }
    event.preventDefault();
    const accepted = await cuteConfirm({icon:button.dataset.confirmIcon,title:button.dataset.confirmTitle,message:button.dataset.confirmMessage,action:button.dataset.confirmAction});
    if (!accepted || !button.form) return;
    button.dataset.confirmed = '1'; button.form.requestSubmit(button);
}));
const languagePreset = document.getElementById('languagePreset');
if (languagePreset) languagePreset.addEventListener('change', function () {
    const form = this.closest('form');
    const fields = ['language_code','language_label','native_label','pano_code','translate_code','webview_code','audio_code','direction','language_flag'];
    const selected = this.selectedOptions[0];
    if (!selected?.dataset.language) { fields.forEach(name => { form.elements[name].value = name === 'direction' ? 'ltr' : ''; }); return; }
    const [code,label,nativeLabel,pano,translate,webview,audio,direction,flag] = JSON.parse(selected.dataset.language);
    [code,label,nativeLabel,pano,translate,webview,audio,direction,flag].forEach((value,index) => { form.elements[fields[index]].value = value; });
    form.requestSubmit();
});
const languageAddForm = document.getElementById('languageAddForm');
if (languageAddForm) languageAddForm.addEventListener('submit', async event => {
    event.preventDefault();
    if (languageAddForm.dataset.running === '1' || !languageAddForm.elements.language_code.value) return;
    const progressBox=document.getElementById('languageAddProgress'),bar=document.getElementById('languageProgressBar'),percent=document.getElementById('languageProgressPercent'),title=document.getElementById('languageProgressTitle'),detail=document.getElementById('languageProgressDetail'),button=languageAddForm.querySelector('[type="submit"]');
    const update=(value,heading,message)=>{bar.value=value;percent.textContent=Math.round(value)+'%';title.textContent=heading;detail.textContent=message;};
    const request=async payload=>{const response=await fetch('add-language-progress.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:languageAddForm.elements.csrf.value,...payload})});const raw=await response.text();let result;try{result=JSON.parse(raw);}catch(_){throw new Error(response.ok?'Respons server tidak valid. Coba ulangi proses.':'Server gagal memproses terjemahan (HTTP '+response.status+').');}if(!response.ok||!result.success)throw new Error(result.message||'Proses bahasa gagal.');return result;};
    languageAddForm.dataset.running='1';button.disabled=true;languagePreset.disabled=true;progressBox.hidden=false;progressBox.classList.remove('error','success');
    const code=languageAddForm.elements.language_code.value;
    try {
        update(5,'Mendaftarkan bahasa…','Membuat slot bahasa pada database, pano.xml, skin, dan object informasi.');
        const initialized=await request({action:'initialize',code,label:languageAddForm.elements.language_label.value,nativeLabel:languageAddForm.elements.native_label.value,flag:languageAddForm.elements.language_flag.value,pano:languageAddForm.elements.pano_code.value,translate:languageAddForm.elements.translate_code.value,webview:languageAddForm.elements.webview_code.value,audio:languageAddForm.elements.audio_code.value,direction:languageAddForm.elements.direction.value});
        const locations=initialized.locations||[],total=Math.max(1,locations.length);let translated=0;
        for(let index=0;index<locations.length;index++){
            const value=15+((index+1)/total)*65;update(value,'Menerjemahkan welcome text…',(index+1)+' / '+locations.length+' lokasi · '+locations[index]);
            const result=await request({action:'translate_location',code,location:locations[index]});translated+=Number(result.translated||0);
        }
        update(85,'Menerjemahkan teks virtual tour…','Memproses teks Pano2VR dan skin HTML.');
        const assets=await request({action:'translate_assets',code});
        update(100,'Bahasa berhasil ditambahkan',translated+' teks lokasi/object dan '+Number(assets.texts||0)+' teks virtual tour diterjemahkan. Memuat ulang…');progressBox.classList.add('success');
        setTimeout(()=>{location.href=location.pathname+'?tab=languages';},1200);
    } catch(error) {
        progressBox.classList.add('error');title.textContent='Proses terhenti';detail.textContent=error.message+' Bahasa yang sudah tersimpan dapat dilanjutkan melalui Translation Center.';button.disabled=false;languagePreset.disabled=false;delete languageAddForm.dataset.running;
    }
});
const guardedEditorForms = Array.from(document.querySelectorAll('form.editor'));
let allowEditorNavigation = false;
const editorFormState = form => JSON.stringify(Array.from(form.elements).filter(field => field.name && !['submit', 'button'].includes(field.type)).map(field => [field.name, ['checkbox', 'radio'].includes(field.type) ? field.checked : field.value]));
const initialEditorStates = new Map(guardedEditorForms.map(form => [form, editorFormState(form)]));
const hasUnsavedEditorChanges = () => guardedEditorForms.some(form => editorFormState(form) !== initialEditorStates.get(form));
guardedEditorForms.forEach(form => {
    const actionBar = form.querySelector(':scope > .actions, :scope > .config-save-bar');
    const saveButtons = Array.from(form.querySelectorAll('button.save[type="submit"],button.save[name="action"]'));
    let status = null;
    if (actionBar) {
        status = document.createElement('span');
        status.className = 'form-change-status';
        status.innerHTML = '<i></i><span>Tidak ada perubahan</span>';
        actionBar.prepend(status);
        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'form-cancel-action';
        cancel.textContent = 'Batal';
        cancel.addEventListener('click', () => { form.reset(); requestAnimationFrame(syncDirtyState); });
        const firstSave = actionBar.querySelector('.save');
        firstSave ? actionBar.insertBefore(cancel, firstSave) : actionBar.append(cancel);
    }
    const syncDirtyState = () => {
        const changed = editorFormState(form) !== initialEditorStates.get(form);
        form.classList.toggle('has-unsaved-changes', changed);
        saveButtons.forEach(button => { button.disabled = !changed; });
        if (status) status.querySelector('span').textContent = changed ? 'Ada perubahan yang belum disimpan' : 'Tidak ada perubahan';
    };
    form.addEventListener('input', syncDirtyState);
    form.addEventListener('change', syncDirtyState);
    form.addEventListener('submit', () => {
        allowEditorNavigation = true;
        saveButtons.forEach(button => { if (!button.disabled) { button.disabled = true; button.dataset.label = button.textContent; button.textContent = 'Menyimpan…'; } });
    });
    syncDirtyState();
});
const unsavedModal = document.getElementById('unsavedModal');
let pendingNavigationUrl = '';
document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (!link || link.target === '_blank' || allowEditorNavigation || !hasUnsavedEditorChanges()) return;
    event.preventDefault();
    pendingNavigationUrl = link.href;
    unsavedModal.classList.add('open');
    unsavedModal.setAttribute('aria-hidden', 'false');
});
document.querySelectorAll('[data-unsaved-stay]').forEach(button => button.addEventListener('click', () => {
    pendingNavigationUrl = '';
    unsavedModal.classList.remove('open');
    unsavedModal.setAttribute('aria-hidden', 'true');
}));
document.querySelector('[data-unsaved-leave]')?.addEventListener('click', () => {
    if (!pendingNavigationUrl) return;
    allowEditorNavigation = true;
    window.location.href = pendingNavigationUrl;
});
const selectAllSkins = document.getElementById('selectAllSkins');
if (selectAllSkins) {
    const skinTargets = Array.from(document.querySelectorAll('[name="skin_targets[]"]:not(:disabled)'));
    selectAllSkins.addEventListener('change', () => skinTargets.forEach(input => { input.checked = selectAllSkins.checked; }));
    skinTargets.forEach(input => input.addEventListener('change', () => { selectAllSkins.checked = skinTargets.length > 0 && skinTargets.every(item => item.checked); }));
}

const dashboardSearch = document.getElementById('dashboardSearch');
if (dashboardSearch) {
    const dashboardFilter = document.getElementById('dashboardFilter');
    const dashboardCards = Array.from(document.querySelectorAll('.tour-status-card'));
    const categoryButtons = Array.from(document.querySelectorAll('[data-dashboard-category]'));
    let activeCategory = 'all';
    const filterDashboard = () => {
        const query = dashboardSearch.value.trim().toLowerCase();
        const status = dashboardFilter?.value || 'all';
        let visible = 0;
        dashboardCards.forEach(card => {
            const score = Number(card.dataset.dashboardScore);
            const statusMatch = status === 'all' || (status === 'complete' ? score === 100 : score < 100);
            const categoryMatch = activeCategory === 'all' || card.dataset.dashboardCategoryRow === activeCategory;
            const searchMatch = !query || (card.dataset.dashboardName || '').includes(query);
            const show = categoryMatch && statusMatch && searchMatch;
            card.hidden = !show;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const emptyRow = document.getElementById('dashboardEmpty');
        if (emptyRow) {
            emptyRow.hidden = visible !== 0;
            emptyRow.style.display = visible === 0 ? '' : 'none';
        }
    };
    categoryButtons.forEach(button => button.addEventListener('click', () => {
        categoryButtons.forEach(item => item.classList.remove('active'));
        button.classList.add('active');
        activeCategory = button.dataset.dashboardCategory;
        filterDashboard();
    }));
    dashboardSearch.addEventListener('input', filterDashboard);
    dashboardFilter?.addEventListener('change', filterDashboard);
    filterDashboard();
    const showDashboardProgress = (message, type = '') => {
        const progress = document.getElementById('bulkProgress');
        if (!progress) return;
        progress.hidden = false;
        progress.className = 'bulk-progress ' + type;
        progress.textContent = message;
        progress.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };
    document.querySelectorAll('[data-open-location]').forEach(button=>button.addEventListener('click',async()=>{const original=button.textContent;button.disabled=true;button.textContent='…';try{const response=await fetch('open-location.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>,path:button.dataset.openLocation})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Folder gagal dibuka.');button.textContent='✓';setTimeout(()=>button.textContent=original,1200);}catch(error){button.textContent='!';button.title=error.message;setTimeout(()=>button.textContent=original,1800);}finally{button.disabled=false;}}));
    document.querySelectorAll('[data-zip-location]').forEach(button=>button.addEventListener('click',async()=>{const original=button.textContent,score=Number(button.dataset.zipScore||0),incomplete=score<100;if(!await cuteConfirm({icon:incomplete?'⚠️':'📦',title:incomplete?'Lokasi belum 100%. Yakin nih?':'Buat ZIP lokasi?',message:(incomplete?'Progres '+score+'%. Masih ada komponen yang belum lengkap. ':'')+'Semua isi folder '+button.dataset.zipLocation+' akan tetap dikemas.',action:incomplete?'Tetap buat ZIP':'Buat ZIP'}))return;button.disabled=true;button.textContent='…';try{showDashboardProgress('Sedang membuat ZIP '+button.dataset.zipLocation+'…');const response=await fetch('zip-location.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>,id_lokasi:button.dataset.zipLocation,path:button.dataset.zipPath})});const raw=await response.text();let result;try{result=JSON.parse(raw);}catch{throw new Error(raw||'Respons server ZIP tidak valid.');}if(!response.ok||!result.success)throw new Error(result.message||'ZIP gagal dibuat.');showDashboardProgress(result.message+' · '+result.files+' file · '+(result.bytes/1048576).toFixed(1)+' MB','success');button.textContent='✓';setTimeout(()=>button.textContent=original,1800);}catch(error){showDashboardProgress('Gagal membuat ZIP: '+error.message,'error');button.textContent='!';button.title=error.message;setTimeout(()=>button.textContent=original,1800);}finally{button.disabled=false;}}));
}

const bulkLocationCheckboxes = Array.from(document.querySelectorAll('.bulk-location'));
if (bulkLocationCheckboxes.length) {
    const bulkCsrf = <?= json_encode($_SESSION['cms_csrf']) ?>;
    const bulkDatabase = <?= json_encode($database, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const bulkTemplateSource = <?= json_encode($skinTemplateId) ?>;
    const bulkProgress = document.getElementById('bulkProgress');
    const selectedLocations = () => bulkLocationCheckboxes.filter(input => input.checked).map(input => input.value);
    const updateBulkCount = () => { document.getElementById('bulkSelectedCount').textContent = selectedLocations().length + ' lokasi dipilih'; };
    bulkLocationCheckboxes.forEach(input => input.addEventListener('change', updateBulkCount));
    document.getElementById('bulkSelectVisible').addEventListener('change', function () {
        document.querySelectorAll('.tour-status-card:not([hidden]) .bulk-location').forEach(input => { input.checked = this.checked; });
        updateBulkCount();
    });
    const showBulkProgress = (message, type = '') => { bulkProgress.hidden = false; bulkProgress.className = 'bulk-progress ' + type; bulkProgress.textContent = message; };
    const runServerBulkAction = async (action, ids) => {
        const response = await fetch('bulk-action.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:bulkCsrf,action,locations:ids})});
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Aksi massal gagal.');
        return result;
    };
    const productionButton = document.getElementById('runBulkProduction');
    const bulkEditModal=document.getElementById('bulkEditModal'),bulkCategoryMode=document.getElementById('bulkCategoryMode'),bulkDroneNodeMode=document.getElementById('bulkDroneNodeMode');
    const closeBulkEdit=()=>{bulkEditModal.classList.remove('open');bulkEditModal.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');};
    document.getElementById('openBulkEdit')?.addEventListener('click',()=>{const ids=selectedLocations();if(!ids.length){showBulkProgress('Pilih minimal satu lokasi terlebih dahulu.','error');return;}if(ids.length>25){showBulkProgress('Bulk Edit dibatasi maksimal 25 lokasi.','error');return;}document.getElementById('bulkEditSelection').textContent=ids.length+' lokasi dipilih';document.getElementById('bulkEditError').hidden=true;document.getElementById('bulkEditResults').hidden=true;bulkEditModal.classList.add('open');bulkEditModal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');});
    bulkEditModal.querySelectorAll('[data-close-bulk-edit]').forEach(button=>button.addEventListener('click',closeBulkEdit));
    bulkCategoryMode.addEventListener('change',()=>{document.getElementById('bulkCategoryValue').disabled=bulkCategoryMode.value!=='set';});
    bulkDroneNodeMode.addEventListener('change',()=>{const input=document.getElementById('bulkDroneNodeValue');input.disabled=bulkDroneNodeMode.value!=='set';if(bulkDroneNodeMode.value==='clear')input.value='';});
    document.getElementById('applyBulkEdit').addEventListener('click',async function(){const ids=selectedLocations(),statusMode=document.getElementById('bulkDroneStatusMode').value,nodeValue=document.getElementById('bulkDroneNodeValue').value.trim(),errorBox=document.getElementById('bulkEditError'),resultsBox=document.getElementById('bulkEditResults');errorBox.hidden=true;if(!ids.length||ids.length>25){errorBox.textContent='Pilihan lokasi harus berjumlah 1–25.';errorBox.hidden=false;return;}if(bulkCategoryMode.value==='keep'&&statusMode==='keep'&&bulkDroneNodeMode.value==='keep'){errorBox.textContent='Pilih minimal satu field untuk diubah.';errorBox.hidden=false;return;}if(bulkDroneNodeMode.value==='set'&&!nodeValue){errorBox.textContent='Isi node drone, atau pilih “Kosongkan node secara eksplisit”.';errorBox.hidden=false;return;}if(!await cuteConfirm({icon:'✎',title:'Terapkan Bulk Edit?',message:'Perubahan eksplisit akan diterapkan ke '+ids.length+' lokasi. Database akan dicadangkan sebelum ditulis.',action:'Terapkan perubahan'}))return;const changes={category:{mode:bulkCategoryMode.value,value:document.getElementById('bulkCategoryValue').value},has_drone:{mode:statusMode==='keep'?'keep':'set',value:statusMode==='set_true'},drone:{mode:bulkDroneNodeMode.value==='keep'?'keep':'set',value:bulkDroneNodeMode.value==='clear'?'':nodeValue}};this.disabled=true;try{const response=await fetch('bulk-action.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:bulkCsrf,action:'bulk_edit',locations:ids,changes})});const text=await response.text();let result;try{result=JSON.parse(text);}catch{throw new Error('Respons server tidak valid.');}if(!response.ok||!result.success)throw new Error(result.message||'Bulk Edit gagal.');resultsBox.hidden=false;resultsBox.innerHTML=result.results.map(item=>'<article class="'+(item.success?'success':'failed')+'"><strong>'+(item.success?'✓ ':'✕ ')+escapeHtml(item.id)+'</strong><span>'+escapeHtml(item.message)+'</span></article>').join('');showBulkProgress(result.message+(result.backup?' Backup: '+result.backup:''),result.results.some(item=>!item.success)?'warning':'success');if(result.reload)setTimeout(()=>window.location.reload(),1800);}catch(error){errorBox.textContent=error.message;errorBox.hidden=false;showBulkProgress('Bulk Edit gagal: '+error.message,'error');}finally{this.disabled=false;}});
    productionButton?.addEventListener('click', async () => {
        const requestedIds = selectedLocations();
        if (!requestedIds.length) { showBulkProgress('Pilih minimal satu lokasi terlebih dahulu.', 'error'); return; }
        if (requestedIds.length > 25) { showBulkProgress('Bulk Production dibatasi maksimal 25 lokasi.', 'error'); return; }
        const ids = requestedIds.filter(id => id !== bulkTemplateSource);
        if (!ids.length) { showBulkProgress('Lokasi yang dipilih hanya sumber template; tidak ada target yang aman diproses.', 'error'); return; }
        const excluded = requestedIds.length - ids.length;
        if (!await cuteConfirm({icon:'🏭',title:'Jalankan Bulk Production?',message:'Urutan Translate → Voice → Skin → Audit akan dijalankan untuk '+ids.length+' lokasi'+(excluded?' ('+excluded+' sumber template dilewati).':'.')+' Error dicatat tanpa menghentikan batch.',action:'Mulai produksi'})) return;
        const resultBox = document.getElementById('bulkProductionResults');
        const stages = ['translate','voice','skin','audit'];
        const labels = {translate:'Translate',voice:'Voice',skin:'Skin',audit:'Audit'};
        const results = Object.fromEntries(ids.map(id => [id, Object.fromEntries(stages.map(stage => [stage,{status:'pending',message:'Menunggu'}]))]));
        const render = () => {
            resultBox.hidden=false;
            resultBox.innerHTML='<table><thead><tr><th>Lokasi</th>'+stages.map(stage=>'<th>'+labels[stage]+'</th>').join('')+'</tr></thead><tbody>'+ids.map(id=>'<tr><th>'+escapeHtml(id)+'</th>'+stages.map(stage=>{const item=results[id][stage];return '<td class="'+item.status+'"><strong>'+escapeHtml(item.status==='success'?'✓ Berhasil':item.status==='failed'?'✕ Gagal':item.status==='running'?'… Proses':'— Menunggu')+'</strong><small>'+escapeHtml(item.message)+'</small></td>';}).join('')+'</tr>').join('')+'</tbody></table>';
        };
        const setStage = (id,stage,status,message) => { results[id][stage]={status,message};render(); };
        const requestJson = async (url, body) => { const response=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});const text=await response.text();let data;try{data=JSON.parse(text);}catch{throw new Error('Respons server tidak valid.');}if(!response.ok||!data.success)throw new Error(data.message||'Operasi gagal.');return data; };
        document.querySelectorAll('[data-bulk-action],#runBulkProduction').forEach(item=>{item.disabled=true;});render();
        let successCount=0, failureCount=0;
        try {
            for (const id of ids) {
                showBulkProgress('Bulk Production · '+id+' · Translate');setStage(id,'translate','running','Menerjemahkan data lokasi');
                try { const data=await runServerBulkAction('translate',[id]);const item=data.results?.[0];if(!item?.success)throw new Error(item?.message||'Terjemahan gagal.');if(item.location)bulkDatabase[id]=item.location;setStage(id,'translate','success',data.message);successCount++; } catch(error){setStage(id,'translate','failed',error.message);failureCount++;}
                showBulkProgress('Bulk Production · '+id+' · Voice');setStage(id,'voice','running','Membuat audio semua bahasa');
                let voiceDone=0,voiceFailed=0;const voiceErrors=[];
                for (const language of <?= json_encode(array_keys($languageRegistry)) ?>) { const text=String(bulkDatabase[id]?.bahasa?.[language]?.welcomeText||'').trim();if(!text){voiceFailed++;voiceErrors.push(language.toUpperCase()+': teks kosong');continue;}try{await requestJson('generate-voice.php',{csrf:bulkCsrf,id_lokasi:id,language,text});voiceDone++;}catch(error){voiceFailed++;voiceErrors.push(language.toUpperCase()+': '+error.message);} }
                setStage(id,'voice',voiceFailed?'failed':'success',voiceDone+' audio dibuat'+(voiceFailed?' · '+voiceErrors.join('; '):''));voiceFailed?failureCount++:successCount++;
                showBulkProgress('Bulk Production · '+id+' · Skin');setStage(id,'skin','running','Sinkronisasi dengan backup');
                try { const data=await runServerBulkAction('skin',[id]);const item=data.results?.[0];if(!item?.success)throw new Error(item?.message||'Sinkronisasi gagal.');setStage(id,'skin','success','Template dan backup diterapkan');successCount++; } catch(error){setStage(id,'skin','failed',error.message);failureCount++;}
                showBulkProgress('Bulk Production · '+id+' · Audit');setStage(id,'audit','running','Memvalidasi endpoint deploy');
                try { const data=await requestJson('health-check.php',{csrf:bulkCsrf,id_lokasi:id});const failedChecks=(data.checks||[]).filter(check=>!check.ok);setStage(id,'audit',data.healthy?'success':'failed',data.healthy?'Semua endpoint sehat':failedChecks.map(check=>check.label+' HTTP '+check.status).join('; '));data.healthy?successCount++:failureCount++;const healthButton=document.querySelector('[data-health-check="'+CSS.escape(id)+'"]');if(healthButton){healthButton.className='health-check-button '+(data.healthy?'healthy':'unhealthy');healthButton.textContent=data.healthy?'✓ Sehat':'✕ Bermasalah';} } catch(error){setStage(id,'audit','failed',error.message);failureCount++;}
            }
            showBulkProgress('Bulk Production selesai: '+successCount+' tahap berhasil, '+failureCount+' tahap gagal/dilewati.',failureCount?'warning':'success');
        } finally { document.querySelectorAll('[data-bulk-action],#runBulkProduction').forEach(item=>{item.disabled=false;}); }
    });
    document.querySelectorAll('[data-bulk-action]').forEach(button => button.addEventListener('click', async function () {
        const ids = selectedLocations();
        if (!ids.length) { showBulkProgress('Pilih minimal satu lokasi terlebih dahulu.', 'error'); return; }
        const action = button.dataset.bulkAction;
        if ((action === 'translate' || action === 'skin') && !await cuteConfirm({icon:action==='skin'?'🎨':'🌐',title:action==='skin'?'Sinkronkan skin massal?':'Terjemahkan lokasi terpilih?',message:'Proses '+ids.length+' lokasi terpilih. File terkait dapat diperbarui dan backup dibuat jika diperlukan.',action:action==='skin'?'Sinkronkan':'Mulai terjemahkan'})) return;
        document.querySelectorAll('[data-bulk-action]').forEach(item => { item.disabled = true; });
        try {
            if (action === 'translate') {
                showBulkProgress('Menerjemahkan ' + ids.length + ' lokasi…');
                const result = await runServerBulkAction(action, ids);
                showBulkProgress(result.message, 'success');
            } else if (action === 'skin') {
                showBulkProgress('Menyiapkan sinkronisasi skin ' + ids.length + ' lokasi…');
                const form=document.createElement('form');form.method='post';form.innerHTML='<input name="csrf"><input name="action" value="sync_skin">';form.elements.csrf.value=bulkCsrf;
                ids.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='skin_targets[]';input.value=id;form.appendChild(input);});document.body.appendChild(form);form.submit();return;
            } else if (action === 'voice') {
                const bulkVoiceLanguages = <?= json_encode(array_keys($languageRegistry)) ?>;
                let done = 0, failed = 0, total = ids.length * bulkVoiceLanguages.length;
                for (const id of ids) for (const language of bulkVoiceLanguages) {
                    showBulkProgress('Generate voice ' + (done + failed + 1) + ' / ' + total + ' · ' + id + ' · ' + language.toUpperCase());
                    const text = String(bulkDatabase[id]?.bahasa?.[language]?.welcomeText || '').trim();
                    if (!text) { failed++; continue; }
                    try { const response=await fetch('generate-voice.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:bulkCsrf,id_lokasi:id,language,text})}); const result=await response.json(); if(!response.ok||!result.success)throw new Error(result.message); done++; } catch(error) { failed++; }
                }
                showBulkProgress(done + ' audio berhasil dibuat' + (failed ? ', ' + failed + ' gagal/dilewati.' : '.'), failed ? 'warning' : 'success');
            } else if (action === 'health') {
                let healthy = 0, unhealthy = 0;
                for (let index=0; index<ids.length; index++) {
                    const id=ids[index];showBulkProgress('Health check '+(index+1)+' / '+ids.length+' · '+id);
                    try { const response=await fetch('health-check.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:bulkCsrf,id_lokasi:id})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message);result.healthy?healthy++:unhealthy++;const healthButton=document.querySelector('[data-health-check="'+CSS.escape(id)+'"]');if(healthButton){healthButton.className='health-check-button '+(result.healthy?'healthy':'unhealthy');healthButton.textContent=result.healthy?'✓ Sehat':'✕ Bermasalah';} } catch(error){unhealthy++;}
                }
                showBulkProgress(healthy+' lokasi sehat, '+unhealthy+' lokasi bermasalah.',unhealthy?'warning':'success');
            }
        } catch(error) { showBulkProgress('Gagal: ' + error.message, 'error'); }
        finally { document.querySelectorAll('[data-bulk-action]').forEach(item => { item.disabled = false; }); }
    }));

    const auditModal = document.getElementById('auditAllModal');
    const auditData = <?= json_encode(array_map(static fn(array $tour): array => ['id'=>$tour['id'],'name'=>$tour['name'],'score'=>$tour['score'],'missing'=>$tour['missing'] ?? []], $dashboardTours), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const renderAuditAll = () => {
        const query=document.getElementById('auditAllSearch').value.trim().toLowerCase(),filter=document.getElementById('auditAllFilter').value;
        const results=auditData.filter(item=>(filter==='all'||(filter==='complete'?item.score===100:item.score<100))&&(!query||(item.name+' '+item.id+' '+item.missing.join(' ')).toLowerCase().includes(query)));
        document.getElementById('auditAllList').innerHTML=results.length?results.map(item=>'<article class="'+(item.score===100?'complete':'incomplete')+'"><div><strong>'+escapeHtml(item.name)+'</strong><small>'+escapeHtml(item.id)+'</small></div><b>'+item.score+'%</b><ul>'+(item.missing.length?item.missing.map(issue=>'<li>'+escapeHtml(issue)+'</li>').join(''):'<li>✓ Semua komponen lengkap</li>')+'</ul><a href="?tab=audit&lokasi='+encodeURIComponent(item.id)+'">Buka audit →</a></article>').join(''):'<div class="dashboard-empty">Tidak ada hasil audit.</div>';
    };
    document.getElementById('openAuditAll').addEventListener('click',()=>{renderAuditAll();auditModal.classList.add('open');auditModal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');});
    document.getElementById('auditAllSearch').addEventListener('input',renderAuditAll);document.getElementById('auditAllFilter').addEventListener('change',renderAuditAll);
    auditModal.querySelectorAll('[data-close-audit-all]').forEach(button=>button.addEventListener('click',()=>{auditModal.classList.remove('open');auditModal.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');}));
}

const panoSaveButton = document.getElementById('savePanoEditor');
if (panoSaveButton) {
    const estDestinationUrls = <?= json_encode(array_map(static fn(string $slug): string => 'https://jakarta.govirtual.id/' . $slug, EST_DESTINATIONS), JSON_UNESCAPED_SLASHES) ?>;
    const normalizeEstName = value => String(value||'').toLowerCase().replace(/\([^)]*est[^)]*\)/g,' ').replace(/\best\.?\b|menit|minute|minutes/g,' ').replace(/[^a-z0-9]+/g,' ').trim();
    const findEstDestination = title => {
        const name=normalizeEstName(title);if(!name)return '';
        let best='',score=0;estDestinationUrls.forEach(url=>{const slug=normalizeEstName(url.split('/').pop());const nameWords=name.split(' '),slugWords=slug.split(' ');const overlap=nameWords.filter(word=>word.length>2&&slugWords.includes(word)).length;const current=overlap*10-(Math.abs(nameWords.length-slugWords.length));if(name.includes(slug)||slug.includes(name)) { best=url;score=999; } else if(current>score){best=url;score=current;}});return score>0?best:'';
    };
    document.querySelectorAll('[data-pano-editor-tab]').forEach(function(button) {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-pano-editor-tab], [data-pano-editor-panel]').forEach(item=>item.classList.remove('active'));
            button.classList.add('active');document.querySelector('[data-pano-editor-panel="'+button.dataset.panoEditorTab+'"]').classList.add('active');
        });
    });
    document.querySelectorAll('[data-hotspot-filter]').forEach(function(button) {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-hotspot-filter]').forEach(item=>item.classList.remove('active'));button.classList.add('active');
            document.querySelectorAll('.pano-edit-card').forEach(card=>{card.hidden=button.dataset.hotspotFilter==='missing'&&!card.classList.contains('needs-attention');});
        });
    });
    let activeTranslationFilter = 'all';
    const refreshTranslationRow = row => {
        const emptyFields = Array.from(row.querySelectorAll('textarea[data-translation-index]:not([hidden])')).filter(field => !field.value.trim());
        row.classList.toggle('needs-translation', emptyFields.length > 0);
        row.querySelectorAll('.translation-fields label').forEach(label => label.classList.toggle('empty-translation', !label.querySelector('textarea').value.trim()));
        let warning = row.querySelector('.translation-warning');
        if (emptyFields.length > 0 && !warning) { warning=document.createElement('span');warning.className='translation-warning';row.querySelector('.translation-source').after(warning); }
        if (warning) { warning.textContent='! '+emptyFields.length+' bahasa kosong';warning.hidden=emptyFields.length===0; }
        row.hidden=activeTranslationFilter==='missing'&&emptyFields.length===0;
    };
    document.querySelectorAll('[data-translation-filter]').forEach(button=>button.addEventListener('click',()=>{
        document.querySelectorAll('[data-translation-filter]').forEach(item=>item.classList.remove('active'));button.classList.add('active');activeTranslationFilter=button.dataset.translationFilter;
        document.querySelectorAll('.translation-row').forEach(refreshTranslationRow);
    }));
    document.getElementById('panoTranslationEditor')?.addEventListener('input',event=>{if(event.target.matches('[data-translation-index]'))refreshTranslationRow(event.target.closest('.translation-row'));});
    document.getElementById('panoHotspotEditor').addEventListener('input', function(event) {
        if (event.target.dataset.panoField === 'title') {
            const card=event.target.closest('.pano-edit-card');
            if (/\best\.?\b/i.test(event.target.value)) { const url=card.querySelector('[data-pano-field="url"]');if(!url.value.trim()){const match=findEstDestination(event.target.value);if(match){url.value=match;url.dispatchEvent(new Event('input',{bubbles:true}));}} }
        }
        if (event.target.dataset.panoField !== 'url') return;
        const card=event.target.closest('.pano-edit-card'), missing=card.querySelector('.missing-info');
        card.classList.toggle('needs-attention', event.target.value.trim() === '');
        if (event.target.value.trim() !== '' && missing) missing.remove();
        if (event.target.value.trim() === '' && !missing) {
            const button=document.createElement('button');button.type='button';button.className='missing-info';button.title='URL hotspot belum diisi';button.textContent='! Link belum diisi';card.querySelector('header').appendChild(button);
        }
    });
    document.querySelectorAll('[data-translate-pano]').forEach(function(button) {
        button.addEventListener('click', async function() {
            const row=button.closest('.translation-row'), source=row.dataset.source;
            button.disabled=true;button.textContent='…';
            try {
                const languageTargets=<?= json_encode(array_reduce(array_keys($languageRegistry), static function(array $map, string $code) use ($languageRegistry): array { $map[$code] = $languageRegistry[$code]['translate']; $map[$languageRegistry[$code]['pano']] = $languageRegistry[$code]['translate']; return $map; }, []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                for (const field of row.querySelectorAll('[data-translation-index]')) {
                    if (field.value.trim()) continue;
                    const language=<?= json_encode($auditResult['languages'] ?? []) ?>[Number(field.dataset.translationIndex)];
                    const target=languageTargets[language]||language;
                    const response=await fetch('https://translate.googleapis.com/translate_a/single?client=gtx&sl=id&tl='+encodeURIComponent(target)+'&dt=t&q='+encodeURIComponent(source));
                    const data=await response.json();field.value=(data[0]||[]).map(part=>part[0]||'').join('');
                }
            } catch(error) { alert('Auto translate gagal: '+error.message); }
            finally { button.disabled=false;button.textContent='✦';refreshTranslationRow(row); }
        });
    });
    panoSaveButton.addEventListener('click',async function(){
        const hotspots=Array.from(document.querySelectorAll('.pano-edit-card')).map(card=>({nodeId:card.dataset.nodeId,id:card.dataset.hotspotId,title:card.querySelector('[data-pano-field="title"]').value,url:card.querySelector('[data-pano-field="url"]').value,target:card.querySelector('[data-pano-field="target"]').value}));
        const translations={};document.querySelectorAll('.translation-row').forEach(row=>{translations[row.dataset.source]=Array.from(row.querySelectorAll('[data-translation-index]')).map(field=>field.value);});
        const progress=document.getElementById('panoEditorProgress');this.disabled=true;progress.textContent='Menyimpan pano.xml…';
        try{const response=await fetch('save-pano.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>,id_lokasi:<?= json_encode($auditId) ?>,hotspots,translations})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Gagal menyimpan.');progress.textContent=result.message;}catch(error){progress.textContent='Gagal: '+error.message;}finally{this.disabled=false;}
    });
}

document.querySelectorAll('.tab').forEach(function (button) {
    button.addEventListener('click', function () {
        document.querySelectorAll('.tab, .language-panel').forEach(function (element) { element.classList.remove('active'); });
        button.classList.add('active');
        document.querySelector('[data-panel="' + button.dataset.tab + '"]').classList.add('active');
    });
});

const translateButton = document.getElementById('autoTranslate');
const locationCategory = document.getElementById('locationCategory');
if (locationCategory) {
    let previousCategory = locationCategory.value;
    const categoryModal=document.getElementById('categoryCreateModal'),categoryInput=document.getElementById('newLocationCategory'),categoryError=document.getElementById('categoryCreateError'),categorySave=document.getElementById('saveLocationCategory');
    const closeCategoryModal=()=>{categoryModal.classList.remove('open');categoryModal.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');locationCategory.value=previousCategory;categoryError.hidden=true;};
    const openCategoryModal=()=>{categoryInput.value='';categoryError.hidden=true;categoryModal.classList.add('open');categoryModal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');setTimeout(()=>categoryInput.focus(),60);};
    document.querySelectorAll('[data-close-category-create]').forEach(item=>item.addEventListener('click',closeCategoryModal));
    locationCategory.addEventListener('focus',()=>{if(locationCategory.value!=='__add__')previousCategory=locationCategory.value;});
    locationCategory.addEventListener('change',()=>{
        if(locationCategory.value!=='__add__'){previousCategory=locationCategory.value;return;}
        openCategoryModal();
    });
    categorySave.addEventListener('click',async()=>{const label=categoryInput.value.trim();if(!label){categoryError.textContent='Nama kategori wajib diisi.';categoryError.hidden=false;return;}categorySave.disabled=true;try{const response=await fetch('project-category.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>,label})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Kategori gagal ditambahkan.');const addOption=locationCategory.querySelector('[value="__add__"]');locationCategory.insertBefore(new Option(result.label,result.slug,true,true),addOption);previousCategory=result.slug;categoryModal.classList.remove('open');categoryModal.setAttribute('aria-hidden','true');document.body.classList.remove('modal-open');}catch(error){categoryError.textContent=error.message;categoryError.hidden=false;}finally{categorySave.disabled=false;}});
    categoryInput.addEventListener('keydown',event=>{if(event.key==='Enter'){event.preventDefault();categorySave.click();}});
}
if (translateButton) {
    translateButton.addEventListener('click', async function () {
        const sourceLocation = document.querySelector('[name="lokasi[id]"]').value.trim();
        const sourceWelcome = document.querySelector('[name="welcomeText[id]"]').value.trim();
        if (!sourceLocation && !sourceWelcome) {
            alert('Isi teks bahasa Indonesia terlebih dahulu.');
            return;
        }
        const targets = <?= json_encode(array_map(static fn(array $language): string => $language['translate'], array_filter($languageRegistry, static fn(string $code): bool => $code !== 'id', ARRAY_FILTER_USE_KEY)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const originalText = translateButton.textContent;
        const translateText = async (text, targetLanguage) => {
            const response = await fetch('https://translate.googleapis.com/translate_a/single?client=gtx&sl=id&tl=' + encodeURIComponent(targetLanguage) + '&dt=t&q=' + encodeURIComponent(text));
            if (!response.ok) throw new Error('Layanan terjemahan tidak merespons.');
            const result = await response.json();
            const translated = (result[0] || []).map(part => part[0] || '').join('');
            if (!translated) throw new Error('Hasil terjemahan tidak tersedia.');
            return translated;
        };
        translateButton.disabled = true;
        try {
            for (const [fieldCode, apiCode] of Object.entries(targets)) {
                translateButton.textContent = 'Menerjemahkan ke ' + fieldCode.toUpperCase() + '…';
                for (const [field, text] of [['lokasi', sourceLocation], ['welcomeText', sourceWelcome]]) {
                    if (!text) continue;
                    document.querySelector('[name="' + field + '[' + fieldCode + ']"]').value = await translateText(text, apiCode);
                }
            }
            document.querySelector('[data-tab="en"]').click();
        } catch (error) {
            alert('Auto translate gagal: ' + error.message);
        } finally {
            translateButton.disabled = false;
            translateButton.textContent = originalText;
        }
    });
}

const panoramaModal = document.getElementById('panoramaModal');
const panoramaGrid = document.getElementById('panoramaGrid');
const droneViewPicker = document.getElementById('droneViewPicker');
const droneViewFrame = document.getElementById('droneViewFrame');
let pendingDroneNode = '', droneViewTimer = 0;
const droneTourUrl = <?= json_encode(isset($detectedTours[$selectedId]) ? projectUrl($detectedTours[$selectedId]['path'] . '/index.html') : '', JSON_UNESCAPED_SLASHES) ?>;
function droneFramePlayer() { try { return droneViewFrame?.contentWindow?.pano || null; } catch (_) { return null; } }
function droneViewValues() { const player=droneFramePlayer();return {pan:Number(player?.getPan?.()||0),tilt:Number(player?.getTilt?.()||0),fov:Number(player?.getFov?.()||70)}; }
function refreshDroneLiveView(){if(!droneViewPicker||droneViewPicker.hidden)return;const value=droneViewValues();document.getElementById('droneLiveView').textContent='Pan '+value.pan.toFixed(1)+'° · Tilt '+value.tilt.toFixed(1)+'° · FoV '+value.fov.toFixed(1)+'°';droneViewTimer=requestAnimationFrame(refreshDroneLiveView);}
document.querySelectorAll('.panorama-option').forEach(function (option) {
    option.addEventListener('click', function () {
        document.querySelectorAll('.panorama-option').forEach(item=>item.classList.remove('selected'));option.classList.add('selected');pendingDroneNode=option.dataset.nodeValue;
        panoramaGrid.hidden=true;droneViewPicker.hidden=false;document.getElementById('panoramaTitle').textContent='Arahkan target view drone';document.getElementById('droneViewLoading').hidden=false;
        droneViewFrame.src=droneTourUrl+(droneTourUrl.includes('?')?'&':'?')+'cms_target_view=1#'+encodeURIComponent(pendingDroneNode);
        droneViewFrame.onload=()=>{
            const wait=()=>{
                const player=droneFramePlayer();
                if(!player){setTimeout(wait,150);return;}
                try{
                    const frameDocument=droneViewFrame.contentDocument;
                    const removeFrameOverlays=()=>{frameDocument?.getElementById('jakarta-skin-overlay')?.remove();frameDocument?.getElementById('jakarta-skin-boot-cover')?.remove();};
                    removeFrameOverlays();
                    if(frameDocument?.documentElement)new MutationObserver(removeFrameOverlays).observe(frameDocument.documentElement,{childList:true,subtree:true});
                    const cover=frameDocument?.getElementById('jakarta-skin-boot-cover');if(cover)cover.remove();
                    if(player.divSkin){player.divSkin.style.display='none';player.divSkin.style.pointerEvents='none';}
                    if(droneViewFrame.contentWindow?.skin?.divSkin){droneViewFrame.contentWindow.skin.divSkin.style.display='none';}
                    player.openNext('{'+pendingDroneNode+'}');
                    player.setLocked?.(false);
                }catch(_){}
                document.getElementById('droneViewLoading').hidden=true;cancelAnimationFrame(droneViewTimer);refreshDroneLiveView();
            };
            wait();
        };
    });
});
function openPanoramaPicker() {
    if (!panoramaModal) return;
    panoramaModal.classList.add('open'); panoramaModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('modal-open');
}
function closePanoramaPicker() {
    if (!panoramaModal) return;
    cancelAnimationFrame(droneViewTimer);if(droneViewFrame)droneViewFrame.src='';if(panoramaGrid)panoramaGrid.hidden=false;if(droneViewPicker)droneViewPicker.hidden=true;const title=document.getElementById('panoramaTitle');if(title)title.textContent='Pilih panorama drone';
    panoramaModal.classList.remove('open'); panoramaModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('modal-open');
}
document.getElementById('droneViewBack')?.addEventListener('click',()=>{cancelAnimationFrame(droneViewTimer);droneViewFrame.src='';droneViewPicker.hidden=true;panoramaGrid.hidden=false;document.getElementById('panoramaTitle').textContent='Pilih panorama drone';});
document.getElementById('saveDroneView')?.addEventListener('click',()=>{const value=droneViewValues();document.getElementById('droneInput').value=pendingDroneNode;document.getElementById('dronePan').value=value.pan.toFixed(2);document.getElementById('droneTilt').value=value.tilt.toFixed(2);document.getElementById('droneFov').value=value.fov.toFixed(2);document.getElementById('droneViewSummary').textContent='Target view: pan '+value.pan.toFixed(1)+'° · tilt '+value.tilt.toFixed(1)+'° · FoV '+value.fov.toFixed(1)+'°';closePanoramaPicker();});
document.getElementById('openPanoramaPicker')?.addEventListener('click', openPanoramaPicker);
document.querySelectorAll('[data-close-panorama]').forEach(function (element) { element.addEventListener('click', closePanoramaPicker); });
document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closePanoramaPicker(); });

const voiceDatabase = <?= json_encode($database, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const oiDatabase = <?= json_encode($oiDatabase, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const detectedWebElements = <?= json_encode($selectedWebElements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const oiPreviewBaseUrl = <?= json_encode($oiPreviewBaseUrl, JSON_UNESCAPED_SLASHES) ?>;
const currentLocationId = <?= json_encode($selectedId) ?>;
const activeProjectBaseUrl = <?= json_encode(projectUrl(), JSON_UNESCAPED_SLASHES) ?>;
const activeAssetsDirectory = <?= json_encode($assetsDirectoryName) ?>;
const voiceCsrf = <?= json_encode($_SESSION['cms_csrf']) ?>;
const video360Form = document.getElementById('video360UploadForm');
if (video360Form) {
    const progress = document.getElementById('video360Progress');
    document.getElementById('launchVideo360Encoder')?.addEventListener('click', async event => {
        const button=event.currentTarget;button.disabled=true;progress.textContent='Menjalankan encoder desktop…';
        try { const body=new FormData();body.append('csrf',voiceCsrf);const response=await fetch('launch-video360-encoder.php',{method:'POST',body});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Encoder gagal dijalankan.');progress.textContent=result.message;progress.className='voice-progress success'; }
        catch(error){progress.textContent=error.message;progress.className='voice-progress error';} finally {button.disabled=false;}
    });
    video360Form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = video360Form.querySelector('[type="submit"]');
        const singleFiles = document.getElementById('video360Files').files;
        if (!singleFiles.length) { progress.textContent = 'Pilih satu video sumber.'; progress.className = 'voice-progress error'; return; }
        button.disabled = true; progress.textContent = 'Mengunggah paket HLS…';
        try {
            const response = await fetch('upload-video360.php', {method:'POST', body:new FormData(video360Form)});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Upload gagal.');
            progress.textContent = result.message; progress.className = 'voice-progress success';
            setTimeout(() => location.reload(), 900);
        } catch (error) { progress.textContent = error.message; progress.className = 'voice-progress error'; button.disabled = false; }
    });
    document.querySelectorAll('[data-video360-preview]').forEach(button => button.addEventListener('click', () => {
        const url = new URL(activeAssetsDirectory + '/360/player/index.html', activeProjectBaseUrl);
        url.searchParams.set('video', button.dataset.video360Preview); url.searchParams.set('autoplay', '1');
        window.open(url.href, '_blank', 'noopener');
    }));
    document.querySelectorAll('[data-video360-copy]').forEach(button => button.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(button.dataset.video360Copy); button.textContent = '✓ Tersalin'; setTimeout(() => button.textContent = '⧉ Salin JS', 1400); }
        catch (_) { window.prompt('Salin JavaScript berikut:', button.dataset.video360Copy); }
    }));
    const video360Action = async data => {
        const body = new FormData(); Object.entries(data).forEach(([key,value]) => body.append(key,value)); body.append('csrf', voiceCsrf);
        const response = await fetch('upload-video360.php', {method:'POST',body}); const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Operasi gagal.'); return result;
    };
    document.querySelectorAll('[data-video360-edit]').forEach(button => button.addEventListener('click', async () => {
        const id=button.dataset.video360Edit,newId=window.prompt('ID video 360 baru:',id); if(!newId||newId===id)return;
        try { await video360Action({action:'rename',video_id:id,new_id:newId}); location.reload(); } catch(error){alert(error.message);}
    }));
    document.querySelectorAll('[data-video360-delete]').forEach(button => button.addEventListener('click', async () => {
        const id=button.dataset.video360Delete;if(!confirm('Hapus video 360 '+id+' beserta seluruh playlist dan segment?'))return;
        try { await video360Action({action:'delete',video_id:id}); location.reload(); } catch(error){alert(error.message);}
    }));
}
const voiceCodes = <?= json_encode(array_map(static fn(array $language): string => $language['audio'], $languageRegistry), JSON_UNESCAPED_UNICODE) ?>;
const voiceLabels = <?= json_encode(cmsLanguages(), JSON_UNESCAPED_UNICODE) ?>;
const voiceAudioFiles = <?= json_encode($voiceAudioAvailability) ?>;

const objectList = document.getElementById('objectList');
if (objectList) {
    let objectRecords = structuredClone(oiDatabase[currentLocationId] || []);
    let selectedObject = objectRecords.length ? 0 : -1;
    const objectForm = document.getElementById('objectForm');
    const objectSearch = document.getElementById('objectSearch');
    const escapeObjectHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
    const blankObject = () => ({route_key:'objectbaru',infoID:'',nodeID:'',targetID:'Element01',category:'umum',arah:'luruskanan',versi:'1',ukuran:'500px',labelTeks:Object.fromEntries(Object.keys(voiceLabels).map(code=>[code,''])),isActive:true,title:''});
    function renderObjectList() {
        const query = objectSearch.value.trim().toLowerCase();
        const visible = objectRecords.map((record,index)=>({record,index})).filter(({record}) => !query || [record.route_key,record.nodeID,record.labelTeks?.id].some(value=>String(value||'').toLowerCase().includes(query)));
        document.getElementById('objectCount').textContent = visible.length + ' dari ' + objectRecords.length + ' object';
        objectList.innerHTML = visible.map(({record,index}) => '<button type="button" class="object-item '+(index===selectedObject?'active':'')+'" data-object="'+index+'"><strong>'+escapeObjectHtml(record.labelTeks?.id||'Tanpa judul')+'</strong><small>'+escapeObjectHtml(record.route_key)+' · '+escapeObjectHtml(record.nodeID||'Tanpa node')+'</small></button>').join('');
        renderObjectForm();
    }
    function renderObjectForm() {
        const record = objectRecords[selectedObject];
        if (!record) { objectForm.innerHTML='<div class="object-empty">Pilih atau tambahkan object informasi.</div>'; return; }
        const input=(field,label,cls='')=>'<label class="'+cls+'"><span>'+label+'</span><input data-object-field="'+field+'" value="'+escapeObjectHtml(record[field])+'"></label>';
        const select=(field,label,values)=>'<label><span>'+label+'</span><select data-object-field="'+field+'">'+values.map(value=>'<option '+(record[field]===value?'selected':'')+'>'+value+'</option>').join('')+'</select></label>';
        const translations=Object.entries(voiceLabels).map(([code,label])=>'<label><span>'+label+'</span><textarea rows="3" data-object-lang="'+code+'">'+escapeObjectHtml(record.labelTeks?.[code]||'')+'</textarea></label>').join('');
        const previewUrl=record.infoID ? oiPreviewBaseUrl+'?info='+encodeURIComponent(record.infoID)+'&lang=id' : '';
        objectForm.innerHTML='<div class="object-form-head"><label class="object-active-toggle"><span>Object</span><span class="toggle-control"><input type="checkbox" data-object-active aria-label="Aktifkan object" '+(record.isActive?'checked':'')+'><i aria-hidden="true"></i></span></label><div class="object-form-actions"><button type="button" class="translate" data-translate-object>✦ Auto Translate</button><button type="button" class="danger" data-delete-object>Hapus</button></div></div><div class="object-detail-layout"><div><div class="object-fields">'+input('route_key','Route Key','span2')+input('title','Title')+input('nodeID','Node ID')+select('targetID','Target ID',Array.from({length:10},(_,index)=>'Element'+String(index+1).padStart(2,'0')))+input('infoID','Info ID')+select('arah','Arah',['luruskanan','luruskiri','kanan','kiri'])+input('ukuran','Ukuran')+input('category','Kategori')+select('versi','Versi',['1'])+'</div><div class="object-translations">'+translations+'</div></div><aside class="object-preview"><header><strong>Preview Info</strong><a href="'+escapeObjectHtml(previewUrl)+'" target="_blank" rel="noopener">Buka ↗</a></header>'+(previewUrl?'<iframe src="'+escapeObjectHtml(previewUrl)+'" title="Preview Object Informasi"></iframe>':'<div>Isi Info ID untuk melihat preview.</div>')+'</aside></div>';
    }
    objectList.addEventListener('click',event=>{const item=event.target.closest('[data-object]');if(item){selectedObject=Number(item.dataset.object);renderObjectList();}});
    objectSearch.addEventListener('input',renderObjectList);
    objectForm.addEventListener('input',event=>{const record=objectRecords[selectedObject];if(!record)return;if(event.target.dataset.objectField)record[event.target.dataset.objectField]=event.target.value;if(event.target.dataset.objectLang)record.labelTeks[event.target.dataset.objectLang]=event.target.value;if(event.target.hasAttribute('data-object-active'))record.isActive=event.target.checked;const item=objectList.querySelector('[data-object="'+selectedObject+'"]');if(item){item.querySelector('strong').textContent=record.labelTeks?.id||'Tanpa judul';item.querySelector('small').textContent=(record.route_key||'')+' · '+(record.nodeID||'Tanpa node');}});
    objectForm.addEventListener('click',async event=>{
        const translate = event.target.closest('[data-translate-object]');
        if (translate) {
            const record=objectRecords[selectedObject], source=(record?.labelTeks?.id||'').trim();
            if (!source) { alert('Isi label bahasa Indonesia terlebih dahulu.'); return; }
            translate.disabled=true;translate.textContent='Menerjemahkan…';
            try {
                for (const [code,target] of Object.entries(<?= json_encode(array_map(static fn(array $language): string => $language['translate'], array_filter($languageRegistry, static fn(string $code): bool => $code !== 'id', ARRAY_FILTER_USE_KEY)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)) {
                    const response=await fetch('https://api.mymemory.translated.net/get?q='+encodeURIComponent(source)+'&langpair=id|'+target);
                    const result=await response.json();
                    if (!response.ok||!result.responseData?.translatedText) throw new Error('Terjemahan '+code.toUpperCase()+' gagal.');
                    record.labelTeks[code]=result.responseData.translatedText;
                }
                renderObjectForm();
            } catch(error) { alert('Auto translate gagal: '+error.message);translate.disabled=false;translate.textContent='✦ Auto Translate'; }
            return;
        }
        if(event.target.closest('[data-delete-object]')&&await cuteConfirm({icon:'🧩',title:'Hapus object ini?',message:'Object akan dihapus dari daftar editor. Perubahan permanen setelah tombol Simpan ditekan.',action:'Ya, hapus object'})){objectRecords.splice(selectedObject,1);selectedObject=Math.min(selectedObject,objectRecords.length-1);renderObjectList();}
    });
    document.getElementById('addObject').addEventListener('click',()=>{objectRecords.push(blankObject());selectedObject=objectRecords.length-1;objectSearch.value='';renderObjectList();});
    document.getElementById('importWebElements').addEventListener('click',()=>{let added=0;detectedWebElements.forEach(element=>{const exists=objectRecords.some(record=>String(record.nodeID||'').replace(/[{}]/g,'').toLowerCase()===String(element.nodeID).toLowerCase()&&String(record.targetID||'').toLowerCase()===String(element.targetID).toLowerCase());if(exists)return;const record=blankObject();record.nodeID='{'+element.nodeID+'}';record.targetID=element.targetID;record.route_key=(element.nodeID+'-'+element.targetID).toLowerCase().replace(/[^a-z0-9_-]/g,'');objectRecords.push(record);added++;});if(added){selectedObject=objectRecords.length-added;objectSearch.value='';renderObjectList();}document.getElementById('objectProgress').textContent=added+' WebElement baru ditambahkan; simpan untuk menerapkan.';});
    document.getElementById('saveObjects').addEventListener('click',async function(){this.disabled=true;const progress=document.getElementById('objectProgress');progress.textContent='Menyimpan…';try{const response=await fetch(location.href,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:voiceCsrf,id_lokasi:currentLocationId,records:objectRecords})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Gagal menyimpan.');progress.textContent=result.message;}catch(error){progress.textContent='Gagal: '+error.message;}finally{this.disabled=false;}});
    renderObjectList();
}

const voiceLocation = document.getElementById('voiceLocation');

function renderVoiceTexts() {
    if (!voiceLocation) return;
    const data = voiceDatabase[voiceLocation.value];
    const container = document.getElementById('voiceLanguages');
    container.innerHTML = '';
    Object.entries(voiceCodes).forEach(function ([code, fileCode]) {
        const item = document.createElement('article');
        item.className = 'voice-language';
        const exists = Boolean(voiceAudioFiles[code]);
        const text = String(data?.bahasa?.[code]?.welcomeText || '');
        const hasText = Boolean(text.trim());
        const audioUrl = activeProjectBaseUrl + encodeURIComponent(activeAssetsDirectory) + '/audio/' + encodeURIComponent(voiceLocation.value) + '/' + fileCode + '.mp3?v=' + Date.now();
        item.innerHTML = '<div class="voice-language-head"><span>' + voiceLabels[code] + ' → ' + fileCode + '.mp3</span><em class="voice-audio-status '+(exists?'exists':'')+'">'+(exists?'Tersedia':'Belum ada')+'</em></div>' +
            '<textarea rows="4" data-voice-code="' + code + '"></textarea>' +
            '<div class="voice-language-actions">'+(!hasText?'<button type="button" class="translate" data-add-welcome="'+code+'" '+(code==='id'?'disabled title="Isi melalui editor lokasi"':'')+'>Tambah Welcome Text</button>':'<button type="button" class="save" data-generate-voice="'+code+'">'+(exists?'Generate Ulang':'Generate VO')+'</button>')+'</div>' +
            '<audio controls preload="metadata" '+(exists?'src="'+audioUrl+'"':'')+'></audio>';
        item.querySelector('textarea').value = text;
        container.appendChild(item);
    });
}

async function generateOneVoice(language) {
    const textarea = document.querySelector('[data-voice-code="' + CSS.escape(language) + '"]');
    const text = textarea?.value.trim() || '';
    if (!text) throw new Error('Welcome text ' + (voiceLabels[language] || language) + ' masih kosong.');
    const response = await fetch('generate-voice.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:voiceCsrf,id_lokasi:voiceLocation.value,language,text})});
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || 'Gagal membuat audio.');
    voiceAudioFiles[language] = true;
}

if (voiceLocation) {
    voiceLocation.addEventListener('change', renderVoiceTexts);
    renderVoiceTexts();
    document.getElementById('voiceLanguages').addEventListener('click', async function (event) {
        const addButton = event.target.closest('[data-add-welcome]');
        const generateButton = event.target.closest('[data-generate-voice]');
        const button = addButton || generateButton;
        if (!button) return;
        const progress = document.getElementById('voiceProgress');
        button.disabled = true;
        try {
            if (addButton) {
                const language = addButton.dataset.addWelcome;
                progress.textContent = 'Menerjemahkan welcome text ke ' + voiceLabels[language] + '…';
                const response = await fetch('add-welcome-text.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:voiceCsrf,id_lokasi:voiceLocation.value,language})});
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'Terjemahan gagal.');
                voiceDatabase[voiceLocation.value].bahasa[language] = voiceDatabase[voiceLocation.value].bahasa[language] || {lokasi:'',welcomeText:''};
                voiceDatabase[voiceLocation.value].bahasa[language].welcomeText = result.text;
                progress.textContent = result.message + ' Sekarang audio dapat dibuat.';
            } else {
                const language = generateButton.dataset.generateVoice;
                progress.textContent = 'Membuat ' + voiceCodes[language] + '.mp3…';
                await generateOneVoice(language);
                progress.textContent = voiceCodes[language] + '.mp3 berhasil dibuat.';
            }
            renderVoiceTexts();
        } catch (error) { progress.textContent = 'Gagal: ' + error.message; button.disabled = false; }
    });
    document.getElementById('generateVoice').addEventListener('click', async function () {
        const button = this;
        const progress = document.getElementById('voiceProgress');
        const textareas = document.querySelectorAll('[data-voice-code]');
        button.disabled = true;
        try {
            let completed = 0, skipped = 0, failed = 0;
            for (const textarea of textareas) {
                const language = textarea.dataset.voiceCode;
                const text = textarea.value.trim();
                if (!text) { skipped += 1; continue; }
                progress.textContent = 'Membuat ' + voiceCodes[language] + '.mp3…';
                try { await generateOneVoice(language); completed += 1; } catch (error) { failed += 1; }
            }
            progress.textContent = completed + ' audio berhasil dibuat · ' + skipped + ' dilewati karena teks kosong' + (failed ? ' · ' + failed + ' gagal' : '') + '.';
            renderVoiceTexts();
        } catch (error) {
            progress.textContent = 'Gagal: ' + error.message;
        } finally {
            button.disabled = false;
        }
    });
}

const videoLocation = document.getElementById('videoLocation');
function refreshVideoStatus() {
    if (!videoLocation || !videoLocation.value) return;
    const path = videoLocation.selectedOptions[0].dataset.path;
    ['mobile', 'desktop'].forEach(function (type) {
        const relativeVideoPath = path.split('/').filter(Boolean).map(encodeURIComponent).join('/') + '/video/' + type + '.webm';
        const videoUrl = new URL(relativeVideoPath, activeProjectBaseUrl);
        videoUrl.searchParams.set('v', Date.now());
        const url = videoUrl.href;
        const preview = document.getElementById(type + 'Preview');
        const status = document.getElementById(type + 'Status');
        fetch(url, { method: 'HEAD', cache: 'no-store' }).then(function (response) {
            if (response.ok) {
                status.textContent = 'Tersedia'; status.className = 'media-status exists'; preview.src = url; preview.style.display = 'block';
            } else {
                status.textContent = 'Belum ada'; status.className = 'media-status missing'; preview.removeAttribute('src'); preview.style.display = 'none';
            }
        }).catch(function () { status.textContent = 'Belum ada'; status.className = 'media-status missing'; preview.style.display = 'none'; });
    });
}
if (videoLocation) {
    videoLocation.addEventListener('change', refreshVideoStatus);
    refreshVideoStatus();
    document.getElementById('uploadVideo').addEventListener('click', async function () {
        const mobile = document.getElementById('mobileFile').files[0];
        const desktop = document.getElementById('desktopFile').files[0];
        const progress = document.getElementById('videoProgress');
        if (!mobile && !desktop) { progress.textContent = 'Pilih minimal satu file WebM.'; return; }
        const form = new FormData();
        form.append('csrf', voiceCsrf); form.append('id_lokasi', videoLocation.value);
        if (mobile) form.append('mobile', mobile); if (desktop) form.append('desktop', desktop);
        this.disabled = true; progress.textContent = 'Mengunggah video…';
        try {
            const response = await fetch('upload-video.php', { method: 'POST', body: form });
            const responseText = await response.text();
            let result;
            try { result = JSON.parse(responseText); }
            catch (_) { throw new Error(response.status === 413 ? 'Ukuran video melebihi batas upload server.' : 'Server mengembalikan respons yang tidak valid (HTTP ' + response.status + ').'); }
            if (!response.ok || !result.success) throw new Error(result.message || 'Upload gagal.');
            progress.textContent = result.message;
            document.getElementById('mobileFile').value = ''; document.getElementById('desktopFile').value = '';
            refreshVideoStatus();
        } catch (error) { progress.textContent = 'Gagal: ' + error.message; }
        finally { this.disabled = false; }
    });
}

const optimizerList = document.getElementById('optimizerList');
if (optimizerList) {
    const optimizerCsrf = <?= json_encode($_SESSION['cms_csrf']) ?>;
    const optimizerLocation = <?= json_encode($selectedId) ?>;
    const optimizerTourPath = <?= json_encode($detectedTours[$selectedId]['path'] ?? '', JSON_UNESCAPED_SLASHES) ?>;
    const optimizerProgress = document.getElementById('optimizerProgress');
    let optimizerImages = [];
    const formatBytes = bytes => bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    const updateOptimizerSelection = () => {
        const selected = optimizerList.querySelectorAll('.optimizer-check:checked').length;
        document.getElementById('optimizerSelected').textContent = selected + ' gambar dipilih';
        const checks = Array.from(optimizerList.querySelectorAll('.optimizer-check'));
        document.getElementById('optimizerSelectAll').checked = checks.length > 0 && checks.every(input => input.checked);
    };
    const renderOptimizerImages = () => {
        const totalBytes = optimizerImages.reduce((sum,image)=>sum+image.bytes,0), recommended = optimizerImages.filter(image=>image.recommended).length;
        document.getElementById('optimizerSummary').textContent = optimizerImages.length + ' gambar · ' + formatBytes(totalBytes) + ' · ' + recommended + ' direkomendasikan';
        optimizerList.innerHTML = optimizerImages.length ? optimizerImages.map((image,index)=>'<label class="optimizer-item '+(image.recommended?'recommended':'')+'"><input type="checkbox" class="optimizer-check" value="'+index+'" '+(image.recommended?'checked':'')+'><span class="optimizer-thumb"><img src="'+escapeHtml(encodeURI(activeProjectBaseUrl+optimizerTourPath+'/'+image.path))+'" loading="lazy" alt=""></span><span class="optimizer-info"><strong>'+escapeHtml(image.path)+'</strong><small>'+image.width+' × '+image.height+' px · '+formatBytes(image.bytes)+'</small></span><em>'+(image.recommended?'Disarankan':'Opsional')+'</em></label>').join('') : '<div class="optimizer-loading">Tidak ada gambar JPG atau PNG yang dapat dioptimasi.</div>';
        optimizerList.querySelectorAll('.optimizer-check').forEach(input=>input.addEventListener('change',updateOptimizerSelection));updateOptimizerSelection();
    };
    const scanOptimizerImages = async () => {
        optimizerProgress.textContent='Memindai aset gambar…';document.getElementById('scanOptimizer').disabled=true;
        try { const response=await fetch('image-optimizer.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:optimizerCsrf,action:'scan',id_lokasi:optimizerLocation})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Pemindaian gagal.');optimizerImages=result.images;renderOptimizerImages();optimizerProgress.textContent='Pemindaian selesai. Pilih gambar yang akan dioptimasi.'; }
        catch(error){optimizerProgress.textContent='Gagal: '+error.message;optimizerList.innerHTML='<div class="optimizer-loading">Pemindaian gagal.</div>';}
        finally{document.getElementById('scanOptimizer').disabled=false;}
    };
    document.getElementById('scanOptimizer').addEventListener('click',scanOptimizerImages);
    document.getElementById('optimizerSelectAll').addEventListener('change',function(){optimizerList.querySelectorAll('.optimizer-check').forEach(input=>{input.checked=this.checked;});updateOptimizerSelection();});
    document.getElementById('runOptimizer').addEventListener('click',async function(){
        const files=Array.from(optimizerList.querySelectorAll('.optimizer-check:checked')).map(input=>optimizerImages[Number(input.value)].path);
        if(!files.length){optimizerProgress.textContent='Pilih minimal satu gambar.';return;}if(!await cuteConfirm({icon:'🖼️',title:'Optimasi '+files.length+' gambar?',message:'Gambar akan dikonversi dan dikompres. File asli disimpan otomatis di folder backup.',action:'Optimasi sekarang'}))return;
        this.disabled=true;optimizerProgress.textContent='Mengoptimasi '+files.length+' gambar…';
        try { const response=await fetch('image-optimizer.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:optimizerCsrf,action:'optimize',id_lokasi:optimizerLocation,files,max_width:Number(document.getElementById('optimizerMaxWidth').value),quality:Number(document.getElementById('optimizerQuality').value)})});const result=await response.json();if(!response.ok||!result.success)throw new Error(result.message||'Optimasi gagal.');const finalMessage=result.message+' Hemat '+formatBytes(result.saved_bytes)+'. Backup: '+result.backup+(result.errors.length?' · '+result.errors.length+' gagal.':'');await scanOptimizerImages();optimizerProgress.textContent=finalMessage; }
        catch(error){optimizerProgress.textContent='Gagal: '+error.message;}finally{this.disabled=false;}
    });
    scanOptimizerImages();
}

const skinPreviewFrame = document.getElementById('skinPreviewFrame');
const loadSkinPreview = document.getElementById('loadSkinPreview');
const skinPreviewModal = document.getElementById('skinPreviewModal');
if (skinPreviewFrame && loadSkinPreview && skinPreviewModal) {
    loadSkinPreview.addEventListener('click', function () {
        if (!skinPreviewFrame.getAttribute('src')) skinPreviewFrame.src = <?= json_encode(projectUrl($skinTemplateId . '/'), JSON_UNESCAPED_SLASHES) ?>;
        skinPreviewModal.classList.add('open');
        skinPreviewModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    });
    const closeSkinPreview = function () {
        skinPreviewModal.classList.remove('open');
        skinPreviewModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        loadSkinPreview.focus({ preventScroll: true });
    };
    skinPreviewModal.querySelectorAll('[data-close-skin-preview]').forEach(function (button) { button.addEventListener('click', closeSkinPreview); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && skinPreviewModal.classList.contains('open')) closeSkinPreview(); });
}

const tourPreviewModal = document.getElementById('tourPreviewModal');
if (tourPreviewModal) {
    const previewDialog = document.getElementById('tourPreviewDialog');
    const previewFrame = document.getElementById('tourPreviewFrame');
    const previewTitle = document.getElementById('tourPreviewTitle');
    const previewDevice = document.getElementById('tourPreviewDevice');
    const previewExternal = document.getElementById('tourPreviewExternal');
    document.querySelectorAll('[data-tour-preview]').forEach(function (button) {
        button.addEventListener('click', function () {
            const url = button.dataset.tourPreview;
            setTourPreviewDevice('laptop-hd');
            previewTitle.textContent = button.dataset.previewTitle || 'Preview Virtual Tour';
            previewExternal.href = url;
            previewFrame.src = url;
            tourPreviewModal.classList.add('open');
            tourPreviewModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        });
    });
    const setTourPreviewDevice = function (device) {
        const profiles = {
            'desktop-fhd': {label:'Desktop Full HD',width:1920,height:1080,kind:'desktop'},
            'laptop-hd': {label:'Laptop HD',width:1366,height:768,kind:'desktop'},
            'desktop-small': {label:'Desktop ringkas',width:1280,height:720,kind:'desktop'},
            'iphone': {label:'iPhone 14',width:390,height:844,kind:'mobile'},
            'android': {label:'Android umum',width:360,height:800,kind:'mobile'},
            'mobile-small': {label:'HP kecil',width:320,height:568,kind:'mobile'}
        };
        const profile = profiles[device] || profiles['laptop-hd'];
        const canvas = document.getElementById('tourPreviewCanvas');
        const mockup = document.getElementById('tourDeviceMockup');
        mockup.className = 'tour-device-mockup ' + profile.kind;
        mockup.style.width = profile.width + 'px';
        mockup.style.height = profile.height + 'px';
        previewDevice.textContent = profile.label + ' · viewport ' + profile.width + ' × ' + profile.height + ' px';
        requestAnimationFrame(() => {
            const availableWidth = Math.max(1, canvas.clientWidth - 56);
            const availableHeight = Math.max(1, canvas.clientHeight - 56);
            const scale = Math.min(1, availableWidth / profile.width, availableHeight / profile.height);
            mockup.style.transform = 'scale(' + scale + ')';
        });
        tourPreviewModal.querySelectorAll('[data-tour-device]').forEach(function (button) { button.classList.toggle('active', button.dataset.tourDevice === device); });
    };
    tourPreviewModal.querySelectorAll('[data-tour-device]').forEach(function (button) { button.addEventListener('click', function () { setTourPreviewDevice(button.dataset.tourDevice); }); });
    const closeTourPreview = function () {
        tourPreviewModal.classList.remove('open');
        tourPreviewModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        previewFrame.src = 'about:blank';
    };
    tourPreviewModal.querySelectorAll('[data-close-tour-preview]').forEach(function (button) { button.addEventListener('click', closeTourPreview); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && tourPreviewModal.classList.contains('open')) closeTourPreview(); });
}

const healthDetailModal = document.getElementById('healthDetailModal');
const healthDetailResults = new Map();
const describeHealthProblem = function (check) {
    if (check.error) return check.error;
    if (check.status === 0) return 'Server tidak dapat dihubungi atau koneksi timeout.';
    if (check.status === 404) return 'File tidak ditemukan pada server deploy.';
    if (check.status === 401 || check.status === 403) return 'Akses ke file ditolak oleh server.';
    if (check.status >= 500) return 'Server deploy mengalami error internal.';
    if (check.status >= 400) return 'Request ditolak dengan HTTP ' + check.status + '.';
    if (check.latency_ms > 3000) return 'File dapat diakses, tetapi respons lebih lambat dari 3 detik.';
    return 'Komponen dapat diakses dengan normal.';
};
const showHealthDetail = function (locationId, result) {
    document.getElementById('healthDetailTitle').textContent = 'Health Check · ' + locationId;
    document.getElementById('healthDetailSubtitle').textContent = 'Diperiksa dalam ' + result.duration_ms + ' ms · ' + result.base_url;
    const summary = document.getElementById('healthDetailSummary');
    summary.className = 'health-detail-summary ' + (result.healthy ? 'healthy' : 'unhealthy');
    summary.innerHTML = '<strong>' + (result.healthy ? '✓ Deploy sehat' : '✕ Deploy bermasalah') + '</strong><span>' + (result.healthy ? 'Semua file utama dapat diakses.' : 'Ada file utama yang tidak dapat diakses. Penyebabnya ditampilkan di bawah.') + '</span>';
    document.getElementById('healthDetailList').innerHTML = result.checks.map(function (check) { return '<article class="' + (check.ok ? 'healthy' : 'unhealthy') + '"><div class="health-check-icon">' + (check.ok ? '✓' : '!') + '</div><div><strong>' + escapeHtml(check.label) + '</strong><small>' + escapeHtml(check.url) + '</small><p>' + escapeHtml(describeHealthProblem(check)) + '</p></div><span>HTTP ' + check.status + '<small>' + check.latency_ms + ' ms</small></span></article>'; }).join('');
    healthDetailModal.classList.add('open'); healthDetailModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('modal-open');
};
if (healthDetailModal) {
    const closeHealthDetail = function () { healthDetailModal.classList.remove('open'); healthDetailModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('modal-open'); };
    healthDetailModal.querySelectorAll('[data-close-health-detail]').forEach(function (button) { button.addEventListener('click', closeHealthDetail); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && healthDetailModal.classList.contains('open')) closeHealthDetail(); });
}
document.querySelectorAll('[data-health-check]').forEach(function (button) {
    button.addEventListener('click', async function () {
        if (healthDetailResults.has(button.dataset.healthCheck)) { showHealthDetail(button.dataset.healthCheck, healthDetailResults.get(button.dataset.healthCheck)); return; }
        button.disabled = true; button.className = 'health-check-button checking'; button.textContent = 'Memeriksa…';
        try {
            const response = await fetch('health-check.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>,id_lokasi:button.dataset.healthCheck})});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Health check gagal.');
            button.className = 'health-check-button ' + (result.healthy ? 'healthy' : 'unhealthy');
            button.textContent = result.healthy ? '✓ Sehat' : '✕ Bermasalah';
            button.title = result.checks.map(check => check.label + ': HTTP ' + check.status + ' · ' + check.latency_ms + ' ms').join('\n');
            healthDetailResults.set(button.dataset.healthCheck, result);
            showHealthDetail(button.dataset.healthCheck, result);
        } catch (error) { button.className = 'health-check-button unhealthy'; button.textContent = '✕ Gagal'; button.title = error.message; }
        finally { button.disabled = false; }
    });
});

const assetDetailModal = document.getElementById('assetDetailModal');
if (assetDetailModal) {
    const assetDetailList = document.getElementById('assetDetailList');
    document.querySelectorAll('[data-asset-details]').forEach(function (button) {
        button.addEventListener('click', function () {
            const assets = JSON.parse(button.dataset.assetDetails || '[]');
            const totalBytes = Number(button.dataset.assetTotal || 0);
            document.getElementById('assetDetailTitle').textContent = 'Ukuran Aset · ' + button.dataset.assetTitle;
            document.getElementById('assetDetailSubtitle').textContent = assets.length ? assets.length + ' aset melewati batas rekomendasi' : 'Semua aset dalam batas rekomendasi';
            document.getElementById('assetDetailTotal').textContent = (totalBytes / 1048576).toFixed(1) + ' MB';
            assetDetailList.innerHTML = assets.length ? assets.map(function (asset) { return '<article><div><strong>' + escapeHtml(asset.name) + '</strong><small>' + (asset.bytes / 1048576).toFixed(1) + ' MB</small></div><span>Perlu optimasi</span></article>'; }).join('') : '<div class="asset-detail-clean">✓ Tidak ada aset besar yang perlu dioptimasi.</div>';
            assetDetailModal.classList.add('open'); assetDetailModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('modal-open');
        });
    });
    const closeAssetDetail = function () { assetDetailModal.classList.remove('open'); assetDetailModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('modal-open'); };
    assetDetailModal.querySelectorAll('[data-close-asset-detail]').forEach(function (button) { button.addEventListener('click', closeAssetDetail); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && assetDetailModal.classList.contains('open')) closeAssetDetail(); });
}
</script>
</body>
</html>
