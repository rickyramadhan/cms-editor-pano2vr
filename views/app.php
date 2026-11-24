<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Virtual Tour — Govirtual</title>
    <script>try { if (localStorage.getItem('cmsRailWide') === '1') document.documentElement.classList.add('rail-wide'); } catch (_) {}</script>
    <!-- Pindah tab terasa instan: halaman tujuan di-render di latar saat kursor diarahkan ke tab (Chrome/Edge). -->
    <script type="speculationrules">{"prerender":[{"where":{"selector_matches":".location-tabs a:not(.disabled), #sidebarLocationList a"},"eagerness":"moderate"}]}</script>
    <script>
    // Setelah ada penyimpanan (request non-GET), render-di-latar dibuang lalu dipasang ulang supaya tab tujuan tidak menampilkan data basi.
    (() => {
        const selector = 'script[type="speculationrules"]', original = document.querySelector(selector); if (!original || !window.fetch) return;
        const rules = original.textContent, nativeFetch = window.fetch;
        window.fetch = function (input, init) {
            const method = String((init && init.method) || (input && input.method) || 'GET').toUpperCase();
            if (method === 'GET') return nativeFetch.apply(this, arguments);
            document.querySelector(selector)?.remove();
            const restore = () => { if (document.querySelector(selector)) return; const script = document.createElement('script'); script.type = 'speculationrules'; script.textContent = rules; document.head.appendChild(script); };
            return nativeFetch.apply(this, arguments).finally(restore);
        };
    })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/cms-components.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/cms-components.css')) ?>">
    <link rel="stylesheet" href="assets/css/smart-hub.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/smart-hub.css')) ?>">
    <link rel="stylesheet" href="assets/css/inspector.css?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/css/inspector.css')) ?>">
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
            <a class="<?= $activeTab === 'inspector' ? 'active' : '' ?>" href="?tab=inspector"><span aria-hidden="true"><i data-lucide="scan-search"></i></span>Inspector</a>
            <?php if ($cmsRuntimeReady): ?><a class="<?= in_array($activeTab, ['lokasi', 'voice', 'video', 'audit', 'object', 'skin'], true) ? 'active' : '' ?>" href="?tab=lokasi&lokasi=<?= e($selectedId) ?>"><span aria-hidden="true"><i data-lucide="settings-2"></i></span>Configuration</a><?php endif; ?>
            <span class="cms-nav-label">Media &amp; Tools</span>
            <?php if ($cmsRuntimeReady): ?><a class="<?= in_array($activeTab, ['languages', 'translations'], true) ? 'active' : '' ?>" href="?tab=languages"><span aria-hidden="true"><i data-lucide="languages"></i></span>Bahasa &amp; Terjemahan</a><?php endif; ?>
            <?php if ($cmsRuntimeReady): ?><a class="<?= $activeTab === 'video360' ? 'active' : '' ?>" href="?tab=video360"><span aria-hidden="true"><i data-lucide="circle-play"></i></span>Video 360</a><?php endif; ?>
            <?php if ($cmsRuntimeReady): ?><a class="<?= $activeTab === 'inventaris' ? 'active' : '' ?>" href="?tab=inventaris"><span aria-hidden="true"><i data-lucide="list-checks"></i></span>Inventaris OI & Video 360</a><?php endif; ?>
            <a class="<?= $activeTab === 'loading' ? 'active' : '' ?>" href="?tab=loading"><span aria-hidden="true"><i data-lucide="gauge"></i></span>Optimasi Loading</a>
            <?php if ($cmsRuntimeReady): ?><a class="<?= $activeTab === 'custom' ? 'active' : '' ?>" href="?tab=custom"><span aria-hidden="true"><i data-lucide="code-2"></i></span>Custom CSS</a><?php endif; ?>
        </nav>
        <?php $cmsMe = cmsCurrentUser(); ?><div class="cms-userbox"><span><i data-lucide="circle-user-round" aria-hidden="true"></i> <?= e($cmsMe['name'] ?? $cmsMe['username'] ?? '') ?></span><small><?= e(CMS_ROLES[$cmsMe['role'] ?? ''] ?? '') ?></small><a href="users.php"><?= cmsIsAdmin() ? 'Pengguna' : 'Akun saya' ?></a><a href="logout.php">Keluar</a></div><div class="cms-navigation-footer"><small>Project aktif</small><strong><?= e($activeProject['name']) ?></strong><span><?= e($projectRoot) ?></span><a href="projects.php"><i data-lucide="repeat-2" aria-hidden="true"></i> Ganti project</a></div>
    </aside>
    <div class="cms-navigation-scrim" id="cmsNavigationScrim" hidden></div>
    <main class="shell">
    <?php $tabLabels = ['dashboard'=>'Dashboard','languages'=>'Manajemen Bahasa','translations'=>'Translation Center','lokasi'=>'Data Lokasi','voice'=>'Voice Over','video'=>'Video','audit'=>'Audit','object'=>'Object Informasi','video360'=>'Video 360','inventaris'=>'Inventaris OI & Video 360','custom'=>'Custom CSS','skin'=>'Template Skin','smart'=>'Smart Hub','aihub'=>'AI Guide Hub','inspector'=>'Inspector','loading'=>'Optimasi Loading']; $pageLabel = $tabLabels[$activeTab] ?? 'Data Lokasi'; ?>
    <header class="topbar">
        <button type="button" class="cms-menu-button" id="openCmsNavigation" aria-controls="cmsNavigation" aria-expanded="false" aria-label="Buka menu"><i data-lucide="menu" aria-hidden="true"></i></button>
        <div class="cms-page-heading"><nav class="cms-breadcrumbs" aria-label="Breadcrumb"><a href="?tab=dashboard">CMS Virtual Tour</a><span>/</span><span aria-current="page"><?= e($pageLabel) ?></span></nav><h1><?= e($pageLabel) ?></h1><small><?= e($activeProject['name']) ?></small></div>
        <div class="topbar-actions"><?php if ($skinTemplateId !== ''): ?><a class="preview" href="<?= e(projectUrl($skinTemplateId . '/')) ?>" target="_blank" rel="noopener"><span aria-hidden="true"><i data-lucide="external-link"></i></span>Pratinjau Tur</a><?php endif; ?><a class="cms-create-action" href="?tab=lokasi&lokasi=baru"><span aria-hidden="true"><i data-lucide="plus"></i></span>Buat Baru</a></div>
    </header>

    <?php if ($message !== ''): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <?php if (!in_array($activeTab, ['dashboard', 'languages', 'translations', 'custom', 'video360', 'inventaris', 'smart', 'aihub', 'inspector', 'loading'], true)): ?>
    <div class="layout">
        <aside class="sidebar card">
            <div class="sidebar-title"><div><h2>Lokasi</h2><small><?= count($detectedTours) ?> tur terdeteksi</small></div><span class="sidebar-title-actions"><button type="button" class="rail-toggle" id="railToggle" aria-pressed="false" aria-label="Lebarkan daftar lokasi" title="Lebarkan daftar lokasi"><svg class="rt-expand" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 4h5v5"/><path d="m20 4-6 6"/><path d="M9 20H4v-5"/><path d="m4 20 6-6"/></svg><svg class="rt-shrink" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 9h-5V4"/><path d="m15 9 6-6"/><path d="M4 15h5v5"/><path d="m9 15-6 6"/></svg></button><a href="?tab=lokasi&lokasi=baru" class="add">＋ Baru</a></span></div>
            <div class="sidebar-tools">
                <input type="search" id="locationSearch" placeholder="Cari lokasi…" aria-label="Cari lokasi">
                <?php if ($sidebarTourStatus !== []): ?>
                <div class="sidebar-sort">
                    <select id="locationSort" aria-label="Urutkan lokasi">
                        <option value="default">Urutan bawaan</option>
                        <option value="score-asc">Persen terendah</option>
                        <option value="score-desc">Persen tertinggi</option>
                        <option value="name">Nama A–Z</option>
                    </select>
                    <select id="locationFilter" aria-label="Saring lokasi">
                        <option value="all">Semua lokasi</option>
                        <option value="complete">Sudah lengkap</option>
                        <option value="incomplete">Belum lengkap</option>
                        <option value="video">Video belum lengkap</option>
                        <option value="audio">Audio belum lengkap</option>
                        <option value="welcome">Welcome text belum lengkap</option>
                        <option value="translations">Terjemahan belum lengkap</option>
                        <option value="suspect">Terjemahan mencurigakan</option>
                        <option value="links">Link hotspot kosong</option>
                        <option value="objects">Object Informasi belum lengkap</option>
                        <option value="unsaved">Belum disimpan</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <nav class="sidebar-location-list" id="sidebarLocationList">
                <?php foreach ($sidebarLocations as $id => $item): $isUnsaved = isset($detectedTours[$id]) && !isset($database[$id]); $tourStatus = $sidebarTourStatus[$id] ?? null; $isComplete = $tourStatus !== null && $tourStatus['score'] === 100; $locationName = $item['bahasa']['id']['lokasi'] ?? humanizeLocationId($id);
                    // data untuk urut/saring di sidebar (cms-shell.js); skor komponen null = komponen tidak berlaku
                    $statusAttrs = '';
                    if ($tourStatus !== null) {
                        $suspects = translationSuspects($database[$id] ?? []);
                        $statusAttrs .= ' data-score="' . (int) $tourStatus['score'] . '" data-suspect="' . count($suspects) . '"';
                        foreach (['video', 'audio', 'welcome', 'translations', 'links', 'objects'] as $part) {
                            $value = $tourStatus['scores'][$part] ?? null;
                            if ($value !== null) $statusAttrs .= ' data-' . $part . '="' . (int) $value . '"';
                        }
                        $issues = array_merge($tourStatus['missing'], $suspects);
                        $statusAttrs .= ' data-issues="' . e(implode("\n", $issues)) . '"';
                    }
                    if ($isUnsaved) $statusAttrs .= ' data-unsaved="1"';
                    $topicStatus = array_filter(locationTopicStatus($tourStatus), static fn(array $topic): bool => in_array($topic['state'], ['miss', 'idle'], true)); // topik yang belum lengkap (+ penanda netral "Drone nonaktif") ?>
                    <a class="location <?= $id === $selectedId ? 'active' : '' ?> <?= $isUnsaved ? 'unsaved' : '' ?> <?= $isComplete ? 'complete' : '' ?>" data-location-category="<?= e(tourCategory($id)) ?>" data-location-search="<?= e(strtolower($locationName . ' ' . $id)) ?>"<?= $statusAttrs ?> href="?tab=<?= e($activeTab) ?>&lokasi=<?= e($id) ?>">
                        <span class="location-title"><strong><?= e($locationName) ?></strong><?php foreach (($tourStatus['hints'] ?? []) as $hintText): ?><i class="hint-info" data-tip="<?= e((string) $hintText) ?>" data-state="hint">i</i><?php endforeach; ?><?php if ($isUnsaved): ?><i>Belum disimpan</i><?php elseif ($isComplete): ?><i class="complete-badge" title="Semua komponen lengkap">✓ Lengkap</i><?php elseif ($tourStatus !== null): ?><i class="progress-badge" title="<?= e(implode("\n", $issues ?? [])) ?>"><?= e($tourStatus['score']) ?>%</i><?php endif; ?></span>
                        <?php if ($topicStatus !== []): $topicShort = ['data' => 'Data', 'voice' => 'Voice Over', 'video' => 'Video', 'audit' => 'Audit', 'object' => 'Object Info', 'drone' => 'Drone']; ?><span class="loc-meta" aria-label="Topik yang belum lengkap"><?php $chipTabs = ['data' => 'lokasi', 'voice' => 'voice', 'video' => 'video', 'audit' => 'audit', 'object' => 'object', 'drone' => 'lokasi']; foreach ($topicStatus as $topicKey => $topic):
                            // klik label = langsung ke tab topik itu (Voice Over & Object Informasi hanya bila data lokasinya sudah ada)
                            $chipHref = (!in_array($topicKey, ['voice', 'object'], true) || isset($database[$id])) ? '?tab=' . $chipTabs[$topicKey] . '&lokasi=' . $id : ''; ?><span class="loc-chip seg-<?= e($topicKey) ?> lv-<?= e($topic['level'] ?? 'partial') ?>" data-tip="<?= e($topic['tip']) ?>" data-state="<?= e($topic['state']) ?>"<?= $chipHref !== '' ? ' data-href="' . e($chipHref) . '" role="link" tabindex="0"' : '' ?>><i></i><?= e($topicShort[$topicKey] ?? $topic['label']) ?></span><?php endforeach; ?></span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
                <div class="sidebar-empty" id="sidebarLocationEmpty" hidden>Tidak ada lokasi yang cocok.</div>
            </nav>
        </aside>
        <script src="assets/js/cms-shell.js?v=<?= e((string) filemtime(dirname(__DIR__) . '/assets/js/cms-shell.js')) ?>"></script>
        <?php $selectedTopics = locationTopicStatus($sidebarTourStatus[$selectedId] ?? null); $tabTopic = static function (string $key) use ($selectedTopics): string { $topic = $selectedTopics[$key] ?? null; return $topic === null ? '' : ' data-topic="' . e($key) . '" data-state="' . e($topic['state']) . '" data-level="' . e((string) ($topic['level'] ?? '')) . '" data-tip="' . e($topic['tip']) . '" data-tip-pos="bottom"'; };
        $tabFlag = static function (string $key) use ($selectedTopics): string {
            if (($selectedTopics[$key]['level'] ?? '') === 'empty') return '<b class="tab-flag" aria-hidden="true">!</b>';
            if ($key === 'data' && ($selectedTopics['drone']['state'] ?? '') === 'miss') return '<b class="tab-flag" aria-hidden="true" title="' . e((string) ($selectedTopics['drone']['tip'] ?? 'Drone belum diatur')) . '">!</b>';
            if (($selectedTopics[$key]['hint'] ?? '') !== '') return '<b class="tab-flag is-info" aria-hidden="true">i</b>';
            return '';
        }; ?>
        <div class="location-workspace">
            <div class="location-toolbar">
            <nav class="location-tabs" aria-label="Bagian pengelolaan lokasi">
                <a<?= $tabTopic('data') ?> class="<?= $activeTab === 'lokasi' ? 'active' : '' ?>" href="?tab=lokasi&amp;lokasi=<?= e($selectedId) ?>" <?= $activeTab === 'lokasi' ? 'aria-current="page"' : '' ?>><i data-lucide="file-text" aria-hidden="true"></i><span><small>Konten</small>Data</span><?= $tabFlag('data') ?></a>
                <a<?= $tabTopic('voice') ?> class="<?= $activeTab === 'voice' ? 'active' : '' ?> <?= isset($database[$selectedId]) ? '' : 'disabled' ?>" href="?tab=voice&amp;lokasi=<?= e($selectedId) ?>" <?= $activeTab === 'voice' ? 'aria-current="page"' : '' ?>><i data-lucide="audio-lines" aria-hidden="true"></i><span><small>Media</small>Voice Over</span><?= $tabFlag('voice') ?></a>
                <a<?= $tabTopic('video') ?> class="<?= $activeTab === 'video' ? 'active' : '' ?> <?= isset($detectedTours[$selectedId]) ? '' : 'disabled' ?>" href="?tab=video&amp;lokasi=<?= e($selectedId) ?>" <?= $activeTab === 'video' ? 'aria-current="page"' : '' ?>><i data-lucide="video" aria-hidden="true"></i><span><small>Media</small>Video</span><?= $tabFlag('video') ?></a>
                <a<?= $tabTopic('audit') ?> class="<?= $activeTab === 'audit' ? 'active' : '' ?> <?= isset($detectedTours[$selectedId]) ? '' : 'disabled' ?>" href="?tab=audit&amp;lokasi=<?= e($selectedId) ?>" <?= $activeTab === 'audit' ? 'aria-current="page"' : '' ?>><i data-lucide="clipboard-check" aria-hidden="true"></i><span><small>Peralatan</small>Audit</span><?= $tabFlag('audit') ?></a>
                <a<?= $tabTopic('object') ?> class="<?= $activeTab === 'object' ? 'active' : '' ?> <?= isset($database[$selectedId]) ? '' : 'disabled' ?>" href="?tab=object&amp;lokasi=<?= e($selectedId) ?>" <?= $activeTab === 'object' ? 'aria-current="page"' : '' ?>><i data-lucide="scan-search" aria-hidden="true"></i><span><small>Konten</small>Object Informasi</span><?= $tabFlag('object') ?></a>
                <a class="location-tab-project <?= $activeTab === 'skin' ? 'active' : '' ?>" href="?tab=skin&amp;lokasi=<?= e($selectedId) ?>" <?= $activeTab === 'skin' ? 'aria-current="page"' : '' ?>><i data-lucide="panel-top" aria-hidden="true"></i><span><small>Project</small>Template Skin</span></a>
            </nav>
            <?php if ($selectedId !== 'baru' && isset($detectedTours[$selectedId])): ?><a class="location-local-tour" href="<?= e(projectUrl(trim($detectedTours[$selectedId]['path'], '/') . '/')) ?>" target="_blank" rel="noopener"><i data-lucide="external-link" aria-hidden="true"></i><span>Kunjungi tur lokal</span></a><?php endif; ?>
            </div>
    <?php endif; ?>

    <?php if (in_array($activeTab, ['languages', 'translations'], true)): ?>
        <nav class="language-section-tabs" aria-label="Menu bahasa dan terjemahan">
            <a class="<?= $activeTab === 'languages' ? 'active' : '' ?>" href="?tab=languages">Manajemen Bahasa</a>
            <a class="<?= $activeTab === 'translations' ? 'active' : '' ?>" href="?tab=translations">Translation Center</a>
        </nav>
    <?php endif; ?>

    <?php if ($activeTab === 'dashboard'): ?>
        <?php $completeTours = count(array_filter($dashboardTours, static fn(array $tour): bool => $tour['data_complete'])); $builtTours = count(array_filter($dashboardTours, static fn(array $tour): bool => $tour['bundle_built'])); $implementedTours = count(array_filter($dashboardTours, static fn(array $tour): bool => $tour['implemented'])); $totalPanorama = array_sum(array_map(static fn(array $tour): int => (int) ($tour['panoramas'] ?? 0), $dashboardTours)); $builtPanorama = array_sum(array_map(static fn(array $tour): int => $tour['bundle_built'] ? (int) ($tour['panoramas'] ?? 0) : 0, $dashboardTours)); ?>
        <section class="dashboard" aria-labelledby="dashboardTitle">
            <div class="dashboard-intro"><div><span class="eyebrow">OVERVIEW</span><h2 id="dashboardTitle">Dashboard Produksi</h2><p>Pantau kesiapan konten dan kelola seluruh lokasi virtual tour dari satu tempat.</p></div><a href="?tab=lokasi&lokasi=baru"><span aria-hidden="true">＋</span> Buat Lokasi Baru</a></div>
            <div class="dashboard-summary">
                <article class="card"><i aria-hidden="true"><span data-lucide="map-pin"></span></i><div><span>Total lokasi</span><strong><?= count($dashboardTours) ?></strong><small>Folder tur terdeteksi</small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="image"></span></i><div><span>Total panorama</span><strong><?= $totalPanorama ?></strong><small><?= $builtPanorama ?> panorama sudah dibangun</small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="info"></span></i><div><span>Object Informasi</span><strong><?= count($inventoryObjects ?? []) ?></strong><small><a href="?tab=inventaris">Lihat daftar</a></small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="circle-play"></span></i><div><span>Video 360</span><strong><?= count($inventoryVideos ?? []) ?></strong><small><a href="?tab=inventaris&jenis=video360">Lihat daftar</a></small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="package-check"></span></i><div><span>Panorama dibangun</span><strong><?= $builtPanorama ?></strong><small><?= $builtTours ?> lokasi bundle · <?= $implementedTours ?> implementasi</small></div></article>
                <article class="card"><i aria-hidden="true"><span data-lucide="circle-check"></span></i><div><span>Data lengkap</span><strong><?= $completeTours ?></strong><small>Profil Smart Kit lengkap</small></div></article>
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
                <div class="dashboard-table-wrap"><table class="dashboard-table"><thead><tr><th class="select-column"></th><th>Lokasi</th><th>Kategori Smart Kit</th><th>Panorama</th><th>OI</th><th>Video 360</th><th>Kelengkapan data</th><th>Bundle</th><th>Implementasi</th><th>URL Local</th><th>URL Deploy</th><th class="dashboard-actions-column">Aksi</th></tr></thead><tbody id="tourStatusList">
                    <?php foreach ($dashboardTours as $tour): ?><tr class="tour-status-card" data-href="?tab=lokasi&lokasi=<?= e($tour['id']) ?>" title="Buka halaman <?= e($tour['name']) ?>" data-location-id="<?= e($tour['id']) ?>" data-dashboard-category-row="<?= e($tour['category']) ?>" data-dashboard-name="<?= e($tour['search_text']) ?>" data-dashboard-score="<?= $tour['score'] ?>">
                        <td class="select-column"><input type="checkbox" class="bulk-location" value="<?= e($tour['id']) ?>" aria-label="Pilih <?= e($tour['name']) ?>"></td><td><a class="dashboard-tour-link" href="?tab=lokasi&lokasi=<?= e($tour['id']) ?>"><strong><?= e($tour['name']) ?></strong><small><?= e($tour['id']) ?></small></a></td><td><span class="smartkit-category"><?= e($tour['category_label']) ?></span></td><td><b class="dashboard-count"><?= (int) ($tour['panoramas'] ?? 0) ?></b></td><td><b class="dashboard-count"><?= (int) ($tour['oi_count'] ?? 0) ?></b></td><td><b class="dashboard-count"><?= (int) ($tour['video360_count'] ?? 0) ?></b></td><td><span class="table-progress"><i style="--progress:<?= $tour['score'] ?>%"></i><b><?= $tour['score'] ?>%</b></span></td>
                        <td><span class="readiness-indicator <?= $tour['bundle_built'] ? 'is-ready' : 'is-pending' ?>" title="<?= $tour['bundle_built'] ? 'Bundle Smart Kit sudah dibuat' : 'Bundle Smart Kit belum dibuat' ?>"><i data-lucide="<?= $tour['bundle_built'] ? 'check' : 'minus' ?>" aria-hidden="true"></i></span></td><td><span class="readiness-indicator <?= $tour['implemented'] ? 'is-ready' : 'is-pending' ?>" title="<?= $tour['implemented'] ? 'Smart Kit sudah diterapkan' : 'Smart Kit belum diterapkan' ?>"><i data-lucide="<?= $tour['implemented'] ? 'check' : 'minus' ?>" aria-hidden="true"></i></span></td>
                        <td><div class="dashboard-preview-links"><button type="button" data-tour-preview="<?= e($tour['local_url']) ?>" data-preview-title="<?= e($tour['name']) ?> · Local">Preview</button></div></td><td><div class="dashboard-preview-links"><button type="button" data-tour-preview="<?= e($tour['deploy_url']) ?>" data-preview-title="<?= e($tour['name']) ?> · Deploy">Preview</button></div></td><td class="dashboard-actions-column"><div class="dashboard-row-actions"><button type="button" data-open-location="<?= e($tour['path']) ?>" title="Buka folder di File Explorer"><i data-lucide="folder-open" aria-hidden="true"></i></button><button type="button" data-zip-location="<?= e($tour['id']) ?>" data-zip-path="<?= e($tour['path']) ?>" data-zip-score="<?= e($tour['score']) ?>" title="ZIP semua isi lokasi"><span>ZIP</span></button><a class="dashboard-manage" href="?tab=lokasi&lokasi=<?= e($tour['id']) ?>">Kelola</a></div></td>
                    </tr><?php endforeach; ?>
                    <tr id="dashboardEmpty" hidden><td colspan="12" class="dashboard-empty">Tidak ada lokasi pada kategori atau filter ini.</td></tr>
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
                // klik baris = buka halaman lokasi; kontrol di dalam baris tetap berfungsi sendiri
                rows.forEach(row => row.addEventListener('click', (event) => {
                    if (event.target.closest('input,button,a,select,label')) return;
                    if (row.dataset.href) window.location.href = row.dataset.href;
                }));
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
        <form method="post" class="editor location-editor">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
            <input type="hidden" name="original_id" value="<?= e($selectedId === 'baru' ? '' : $selectedId) ?>">

            <section class="card section loc-data">
                <div class="section-heading"><div><span class="step">01</span><h2>Data lokasi</h2></div><p>Disimpan ke global-database.js</p></div>
                <div class="grid two">
                    <div class="loc-sub loc-id">
                        <div class="loc-sub-head"><div><strong>ID lokasi</strong><small>Nama unik lokasi (huruf, angka, - dan _). Dipakai sebagai nama folder dan alamat tur.</small></div></div>
                        <label class="loc-id-field"><span class="sr-only">ID lokasi</span><input required name="id_lokasi" pattern="[A-Za-z0-9_-]+" value="<?= e($selected['id_lokasi']) ?>" placeholder="contoh: monumennasional"></label>
                    </div>
                    <?php
                        $dronePanoramaMap = [];
                        foreach ($selectedPanoramas as $dronePanorama) $dronePanoramaMap[(string) $dronePanorama['value']] = ['title' => (string) ($dronePanorama['title'] !== '' ? $dronePanorama['title'] : $dronePanorama['nodeId']), 'node' => (string) $dronePanorama['nodeId'], 'thumb' => (string) $dronePanorama['thumbnail']];
                        $droneOn = !empty($selected['has_drone']);
                        $droneHotspotTotal = isset($detectedTours[$selectedId]) ? tourDroneHotspotCount($projectRoot, $detectedTours[$selectedId]) : 0;
                    ?>
                    <div class="loc-sub drone-block<?= $droneOn ? '' : ' is-off' ?>" id="droneBlock" data-hotspots="<?= (int) $droneHotspotTotal ?>" data-panoramas="<?= e(json_encode($dronePanoramaMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
                        <div class="drone-head loc-sub-head">
                            <div><strong>Drone</strong><small>Panorama udara yang dibuka saat ikon drone ditekan di tur, beserta arah pandang awalnya. Saat Anda menyimpan, arah ini ikut diterapkan ke hotspot drone di tur.</small></div>
                            <div class="drone-head-side"><span class="drone-status" id="droneStatus" role="status" hidden></span>
                            <label class="drone-switch"><span class="drone-switch-text" id="droneSwitchText"><?= $droneOn ? 'Aktif' : 'Nonaktif' ?></span><span class="toggle-control"><input type="checkbox" name="has_drone" value="1" aria-label="Aktifkan fitur drone" <?= $droneOn ? 'checked' : '' ?>><i aria-hidden="true"></i></span></label></div>
                        </div>
                        <p class="drone-notice" id="droneNotice" hidden></p>
                        <div class="drone-body">
                            <button type="button" class="drone-thumb" id="droneThumb" aria-label="Lihat pratinjau panorama drone" title="Klik untuk pratinjau" disabled><img alt="" hidden><span class="drone-thumb-zoom" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></span><span class="drone-thumb-empty"><svg viewBox="0 0 24 24"><path d="M8 8l8 8M16 8l-8 8"/><circle cx="6" cy="6" r="2.6"/><circle cx="18" cy="6" r="2.6"/><circle cx="6" cy="18" r="2.6"/><circle cx="18" cy="18" r="2.6"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.4"/></svg></span></button>
                            <div class="drone-info">
                                <b id="droneTitle">Belum ada panorama drone</b>
                                <div class="drone-node"><span>Node</span><input id="droneInput" name="drone" value="<?= e($selected['drone']) ?>" placeholder="Node ID / Custom Node ID" aria-label="Node ID atau Custom Node ID panorama drone"></div>
                                <div class="drone-chips" id="droneChips"></div>
                            </div>
                            <div class="drone-actions">
                                <button type="button" class="drone-btn" id="openPanoramaPicker" onclick="const modal=document.getElementById('panoramaModal');if(modal){modal.classList.add('open');modal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="m3 15 5-5 4 4 3-3 6 6"/></svg>Ganti panorama</button>
                                <button type="button" class="drone-btn is-primary" id="droneSetView"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 3v4M12 17v4M3 12h4M17 12h4"/></svg>Atur arah pandang</button>
                            </div>
                        </div>
                        <input type="hidden" id="dronePan" name="drone_pan" value="<?= e($selected['drone_pan'] ?? 0) ?>"><input type="hidden" id="droneTilt" name="drone_tilt" value="<?= e($selected['drone_tilt'] ?? 0) ?>"><input type="hidden" id="droneFov" name="drone_fov" value="<?= e($selected['drone_fov'] ?? 70) ?>">
                        <small id="droneViewSummary" hidden>Target view: pan <?= e($selected['drone_pan'] ?? 0) ?>° · tilt <?= e($selected['drone_tilt'] ?? 0) ?>° · FoV <?= e($selected['drone_fov'] ?? 70) ?>°</small>
                        <script>
                        (() => {
                            const block = document.getElementById('droneBlock'); if (!block) return;
                            const map = JSON.parse(block.dataset.panoramas || '{}'), input = document.getElementById('droneInput'), toggle = block.querySelector('input[name="has_drone"]');
                            const statusPill = document.getElementById('droneStatus'), notice = document.getElementById('droneNotice'), hotspotTotal = Number(block.dataset.hotspots || 0);
                            const title = document.getElementById('droneTitle'), image = block.querySelector('.drone-thumb img'), chips = document.getElementById('droneChips'), switchText = document.getElementById('droneSwitchText'), setView = document.getElementById('droneSetView');
                            const thumbButton = document.getElementById('droneThumb'), num = id => Number(document.getElementById(id)?.value || 0);
                            // klik gambar = pratinjau besar (hanya melihat, tidak mengubah apa pun)
                            const lightbox = (() => {
                                let box = document.getElementById('cmsLightbox');
                                if (!box) {
                                    box = document.createElement('div'); box.id = 'cmsLightbox'; box.className = 'cms-lightbox'; box.setAttribute('role', 'dialog'); box.setAttribute('aria-modal', 'true'); box.setAttribute('aria-hidden', 'true');
                                    box.innerHTML = '<figure><button type="button" class="cms-lightbox-close" aria-label="Tutup pratinjau">\u00d7</button><img alt=""><figcaption></figcaption></figure>';
                                    document.body.append(box);
                                    const close = () => { box.classList.remove('open'); box.setAttribute('aria-hidden', 'true'); };
                                    box.addEventListener('click', event => { if (event.target === box || event.target.closest('.cms-lightbox-close')) close(); });
                                    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
                                }
                                return box;
                            })();
                            thumbButton.addEventListener('click', () => {
                                const value = input.value.trim(), panorama = map[value]; if (!panorama || !panorama.thumb) return;
                                lightbox.querySelector('img').src = panorama.thumb; lightbox.querySelector('img').alt = panorama.title;
                                lightbox.querySelector('figcaption').innerHTML = '<b></b><span></span>';
                                lightbox.querySelector('figcaption b').textContent = panorama.title;
                                lightbox.querySelector('figcaption span').textContent = 'Node ' + panorama.node + ' \u00b7 Pan ' + num('dronePan').toFixed(1) + '\u00b0 \u00b7 Tilt ' + num('droneTilt').toFixed(1) + '\u00b0 \u00b7 FoV ' + num('droneFov').toFixed(1) + '\u00b0';
                                lightbox.classList.add('open'); lightbox.setAttribute('aria-hidden', 'false');
                            });
                            const sync = () => {
                                const value = input.value.trim(), panorama = map[value];
                                block.classList.toggle('is-empty', value === ''); block.classList.toggle('is-off', !toggle.checked); switchText.textContent = toggle.checked ? 'Aktif' : 'Nonaktif';
                                title.textContent = panorama ? panorama.title : (value === '' ? 'Belum ada panorama drone' : 'Panorama tidak ada di daftar tur');
                                if (panorama && panorama.thumb) { image.src = panorama.thumb; image.hidden = false; } else { image.hidden = true; image.removeAttribute('src'); }
                                chips.innerHTML = value === '' ? '' : '<span><i>Pan</i>' + num('dronePan').toFixed(1) + '\u00b0</span><span><i>Tilt</i>' + num('droneTilt').toFixed(1) + '\u00b0</span><span><i>FoV</i>' + num('droneFov').toFixed(1) + '\u00b0</span>';
                                setView.disabled = value === '' || !map[value];
                                thumbButton.disabled = !(panorama && panorama.thumb);
                                // Status pengaturan drone (sama dengan penilaian di daftar Lokasi): beri tahu bila belum beres
                                const viewIsDefault = Math.abs(num('dronePan')) < .005 && Math.abs(num('droneTilt')) < .005 && [70, 100].includes(Math.round(num('droneFov')));
                                let state = 'ok', label = 'Siap dipakai', message = '';
                                if (!toggle.checked && hotspotTotal === 0) { state = 'idle'; label = 'Belum diatur'; message = 'Lokasi ini belum memakai drone. Nyalakan toggle Aktif bila tur punya panorama udara, lalu pilih panorama dan arah pandangnya.'; }
                                else if (value === '') { state = 'empty'; label = 'Belum diatur'; message = (hotspotTotal ? 'Tur ini punya ' + hotspotTotal + ' hotspot drone, tetapi panorama drone belum dipilih. ' : '') + 'Klik \u201cGanti panorama\u201d lalu \u201cAtur arah pandang\u201d.'; }
                                else if (!panorama) { state = 'empty'; label = 'Panorama tidak ditemukan'; message = 'Node \u201c' + value + '\u201d tidak ada di tur ini. Pilih ulang lewat \u201cGanti panorama\u201d.'; }
                                else if (!toggle.checked) { state = 'warn'; label = 'Belum diaktifkan'; message = 'Panorama drone sudah dipilih, tetapi tombol Aktif masih mati sehingga tur belum memakainya.'; }
                                else if (viewIsDefault) { state = 'warn'; label = 'Arah pandang belum diatur'; message = 'Klik \u201cAtur arah pandang\u201d agar drone terbuka menghadap area yang tepat.'; }
                                block.dataset.status = state;
                                statusPill.hidden = false; statusPill.className = 'drone-status is-' + state; statusPill.textContent = label;
                                notice.hidden = message === ''; notice.textContent = message; notice.className = 'drone-notice is-' + state;
                            };
                            input.addEventListener('input', sync); toggle.addEventListener('change', sync);
                            // "Atur arah pandang": buka pemilih lalu langsung masuk ke mode atur arah untuk panorama yang sudah dipilih
                            setView.addEventListener('click', () => {
                                openPanoramaPicker();
                                const option = Array.from(document.querySelectorAll('.panorama-option')).find(item => item.dataset.nodeValue === input.value.trim());
                                if (option) option.click();
                            });
                            sync();
                        })();
                        </script>
                    </div>
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

            <section class="card section loc-lang">
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

            <section class="card section loc-preview">
                <div class="welcome-preview" id="welcomePreview">
                    <div class="welcome-preview-head"><strong>Pratinjau di tur</strong><small>Kartu sambutan persis seperti di virtual tour (mengikuti skin wilayah ini), tanpa bendera bahasa. Ikut berubah saat Anda mengetik atau berpindah bahasa.</small></div>
                    <div class="welcome-preview-stage" id="welcomePreviewStage"><iframe id="welcomePreviewFrame" title="Pratinjau welcome text" src="api.php?action=welcome-preview&amp;id_lokasi=<?= e(rawurlencode($selectedId)) ?>"></iframe></div>
                </div>
                <script>
                (() => {
                    const frame = document.getElementById('welcomePreviewFrame'), stage = document.getElementById('welcomePreviewStage'); if (!frame) return;
                    let last = '';
                    // iframe dirender selebar layar desktop (1100px) lalu diperkecil agar tampilannya persis seperti di tur, tetapi muat di kolom ini
                    const fit = () => stage.style.setProperty('--s', (stage.clientWidth / 1100).toFixed(4));
                    new ResizeObserver(fit).observe(stage); fit();
                    const activeCode = () => document.querySelector('.tabs .tab.active')?.dataset.tab || 'id';
                    const push = (force = false) => {
                        const code = activeCode(), text = document.querySelector('[name="welcomeText[' + code + ']"]')?.value || '', name = document.querySelector('[name="lokasi[' + code + ']"]')?.value || '';
                        const key = code + '' + name + '' + text; if (!force && key === last) return; last = key;
                        frame.contentWindow?.postMessage({type: 'welcome-preview', lang: code, text, name}, location.origin);
                    };
                    window.addEventListener('message', event => { if (event.origin === location.origin && event.data?.type === 'welcome-preview-ready') push(true); });
                    frame.addEventListener('load', () => push(true));
                    document.addEventListener('input', event => { if (event.target.matches('[name^="welcomeText["],[name^="lokasi["]')) push(); });
                    document.querySelectorAll('.tabs .tab').forEach(button => button.addEventListener('click', () => setTimeout(push, 0)));
                    setInterval(push, 1000);   // menangkap perubahan non-ketik (mis. Auto Translate)
                })();
                </script>
            </section>

            <div class="actions">
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
    <?php elseif ($activeTab === 'inspector'): ?>
        <?php if ($inspectorState !== null) require __DIR__ . '/inspector.php'; ?>
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
        <?php
        $activeSkinTemplate = $skinTemplates[$activeSkinTemplateId] ?? null;
        $skinPreviewUrl = $activeSkinTemplate['preview'] ?? '';
        if ($skinPreviewUrl === '' && $activeSkinTemplate) foreach ($skinStatuses as $previewId => $previewStatus) if ($previewStatus['skinMatches']) { $skinPreviewUrl = projectUrl($detectedTours[$previewId]['path'] . '/'); break; }
        ?>
        <form method="post" class="editor skin-sync-editor" data-action-form>
            <input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>">
            <section class="card section">
                <div class="section-heading"><div><span class="step">SK</span><div><h2>Template Skin</h2><p>Pilih template dari folder <code>skin-templates/</code> di CMS, lalu terapkan ke lokasi project ini.</p></div></div><span class="skin-template-badge"><?= $activeSkinTemplate ? 'Aktif: ' . e($activeSkinTemplate['name']) : 'Belum ada template dipilih' ?></span></div>
                <?php if ($skinTemplates === []): ?>
                    <div class="skin-notice"><strong>Belum ada template</strong><span>Buat folder di <code>cms-virtual-tour/skin-templates/&lt;nama&gt;/</code> berisi <code>skin.js</code> (dan <code>pano2vr_player.js</code>). Panduan: <code>skin-templates/README.md</code>.</span></div>
                <?php else: ?>
                    <div class="skin-sync-list">
                        <?php foreach ($skinTemplates as $template): ?>
                            <label class="skin-sync-item <?= $template['id'] === $activeSkinTemplateId ? 'synced' : '' ?>">
                                <input type="radio" name="skin_template" value="<?= e($template['id']) ?>" <?= $template['id'] === $activeSkinTemplateId ? 'checked' : '' ?>>
                                <span><strong><?= e($template['name']) ?></strong><small><?= e($template['description'] !== '' ? $template['description'] : 'skin-templates/' . $template['id']) ?></small><small><?= e(($template['ggsk'] !== '' ? $template['ggsk'] . ' · ' : '') . ($template['generated'] !== '' ? substr($template['generated'], 0, 10) . ' · ' : '') . 'skin.js tersimpan') ?></small></span>
                                <?php if ($template['id'] === $activeSkinTemplateId): ?><em>Aktif</em><?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="actions"><button class="translate" name="action" value="select_skin_template">Simpan pilihan template</button></div>
                <?php endif; ?>
            </section>
            <?php if ($activeSkinTemplate): ?>
            <section class="card section">
                <div class="section-heading"><div><span class="step">02</span><div><h2>Terapkan ke Lokasi</h2><p>Centang lokasi yang akan memakai template <?= e($activeSkinTemplate['name']) ?>.</p></div></div></div>
                <?php if ($skinPreviewUrl !== ''): ?>
                <div class="skin-preview">
                    <div class="skin-preview-heading">
                        <div><strong>Preview Template <?= e($activeSkinTemplate['name']) ?></strong><small><?= e($skinPreviewUrl) ?></small></div>
                        <div class="skin-preview-actions"><button type="button" id="loadSkinPreview">Lihat preview</button><a href="<?= e($skinPreviewUrl) ?>" target="_blank" rel="noopener">Buka penuh ↗</a></div>
                    </div>
                    <p>Preview dibuka sebagai popup agar posisi halaman CMS tetap terkunci.</p>
                </div>
                <div class="skin-preview-modal" id="skinPreviewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="skinPreviewTitle" data-preview-url="<?= e($skinPreviewUrl) ?>">
                    <div class="skin-preview-backdrop" data-close-skin-preview></div>
                    <div class="skin-preview-dialog">
                        <div class="skin-preview-modal-heading"><div><strong id="skinPreviewTitle">Preview Template <?= e($activeSkinTemplate['name']) ?></strong><small>skin-templates/<?= e($activeSkinTemplate['id']) ?></small></div><button type="button" data-close-skin-preview aria-label="Tutup preview">×</button></div>
                        <iframe id="skinPreviewFrame" title="Preview Template <?= e($activeSkinTemplate['name']) ?>" allow="fullscreen; autoplay" allowfullscreen></iframe>
                    </div>
                </div>
                <?php endif; ?>
                <div class="skin-sync-toolbar"><label><input type="checkbox" id="selectAllSkins"> Pilih semua lokasi</label><span><?= count(array_filter($skinStatuses, static fn(array $s): bool => $s['same'])) ?> / <?= count($skinStatuses) ?> lokasi sudah memakai template ini</span></div>
                <div class="skin-sync-list">
                    <?php foreach ($skinStatuses as $tourId => $status): ?>
                        <label class="skin-sync-item <?= $status['same'] ? 'synced' : 'outdated' ?>">
                            <input type="checkbox" name="skin_targets[]" value="<?= e($tourId) ?>" <?= $status['same'] ? '' : 'checked' ?>>
                            <span><strong><?= e(humanizeLocationId($tourId)) ?></strong><small><?= e(($status['skinMatches'] ? 'Sudah memakai ' . $activeSkinTemplate['name'] : ($status['currentTemplate'] !== '' ? 'Memakai template ' . $status['currentTemplate'] : 'Skin lain / versi lama')) . (empty($status['loaderReady']) ? ' · index.html belum sesuai template (pemuat / tag lama)' : '')) ?></small></span>
                            <em><?= $status['same'] ? 'Sesuai' : 'Perlu' ?></em>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="skin-notice"><strong>Yang diganti</strong><span>Hanya <code>skin.js</code> tersimpan yang diterapkan ke lokasi tercentang. Skin lama dicadangkan ke <code>.skin-backups</code> dan dikembalikan otomatis bila penyalinan gagal. Player, panorama, peta, tiles, video, aset, dan data lokasi tidak disentuh.</span></div>
            </section>
            <div class="actions"><button class="save" name="action" value="sync_skin" data-cute-confirm data-confirm-icon="✨" data-confirm-title="Terapkan template <?= e($activeSkinTemplate['name']) ?>?" data-confirm-message="skin.js lokasi tercentang akan diganti dengan template <?= e($activeSkinTemplate['name']) ?>. File lama dicadangkan otomatis." data-confirm-action="Terapkan">Terapkan Template ke Lokasi Terpilih</button></div>
            <?php endif; ?>
        </form>
        <form method="post" id="projectTemplateForm"><input type="hidden" name="csrf" value="<?= e($_SESSION['cms_csrf']) ?>"><input type="hidden" name="project_action" value="set_template"></form>
    <?php elseif ($activeTab === 'object'): ?>
        <?php $oiStudio = isset($detectedTours[$selectedId]) && $selectedPanoramas !== []; ?>
        <section class="card section object-editor<?= $oiStudio ? ' oi-studio' : '' ?>">
            <div class="section-heading"><div><span class="step">OI</span><h2>Object Informasi</h2><?php if ($oiStudio): ?><div class="oi-stats" id="objectCount" aria-live="polite"></div><?php endif; ?></div><div class="object-head-actions"><button type="button" class="save object-save" id="saveObjects" title="Simpan semua perubahan (Ctrl+S). Perubahan juga disimpan otomatis saat berpindah object."><span class="oi-save-label">Simpan</span><kbd>Ctrl+S</kbd></button></div></div>
            <?php if ($oiStudio): ?>
            <div class="object-panorama-editor">
                <div class="object-panorama-toolbar">
                    <div><strong>Editor Panorama</strong><small>Pilih object di kiri, geser panorama, lalu tarik pin WebElement untuk mengubah posisinya.</small>
                        <div class="oi-legend" aria-label="Keterangan ikon pin"><span class="oi-legend-item"><i class="pin-info"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><circle cx="12" cy="7.8" r=".6" fill="currentColor"/></svg></i>Objek Info</span><span class="oi-legend-item"><i class="pin-land"><svg viewBox="0 0 24 24"><path d="M12 3l7 17-7-4-7 4z" fill="currentColor" fill-opacity=".28"/></svg></i>Hotspot</span><span class="oi-legend-item"><i class="pin-drone"><svg viewBox="0 0 24 24"><path d="M8 8l8 8M16 8l-8 8"/><circle cx="6" cy="6" r="2.6"/><circle cx="18" cy="6" r="2.6"/><circle cx="6" cy="18" r="2.6"/><circle cx="18" cy="18" r="2.6"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.4"/></svg></i>Drone</span></div></div>
                    <label><span>Panorama</span><select id="objectPanoramaNode"><?php foreach ($selectedPanoramas as $panorama): ?><option value="<?= e($panorama['nodeId']) ?>" data-public-node="<?= e($panorama['value']) ?>"><?= e($panorama['title'] ?: $panorama['value']) ?></option><?php endforeach; ?></select></label>
                    <div class="oi-history" role="group" aria-label="Riwayat perubahan"><button type="button" class="translate" id="oiUndo" disabled title="Tidak ada yang bisa diurungkan"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg><span>Urungkan</span></button><button type="button" class="translate" id="oiRedo" disabled title="Tidak ada yang bisa diulang"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 14 5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/></svg><span>Ulangi</span></button></div>
                    <button type="button" class="translate" id="addWebElement" title="Tambah pin Objek Info di tengah pandangan panorama"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg><span>Objek Info</span></button>
                    <button type="button" class="danger" id="removeWebElement" disabled title="Hapus Objek Info terpilih beserta pin dan card-nya (bisa diurungkan dengan Ctrl+Z)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg><span>Hapus Objek Info</span></button>
                    <button type="button" class="oi-preview-btn" id="previewInTour" title="Buka tur sungguhan langsung di panorama ini, lengkap dengan skin dan Object Info, menghadap objek yang dipilih"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><span>Pratinjau di tur</span></button>
                </div>
                <div class="oi-stage-wrap<?= empty($oiDatabase[$selectedId] ?? []) ? ' oi-empty' : '' ?>" id="oiStageWrap">
                    <div class="object-panorama-stage" id="objectPanoramaStage"><div id="objectPanoramaHost"></div><div class="object-panorama-loading" id="objectPanoramaLoading">Memuat panorama editor…</div></div>
                    <div class="oi-overlay">
                        <aside class="object-list-panel"><input id="objectSearch" type="search" placeholder="Cari object…"><div id="objectList"></div></aside>
                        <button type="button" class="oi-collapse oi-collapse-left" data-oi-collapse="left" aria-label="Sembunyikan daftar object" title="Sembunyikan / tampilkan daftar">‹</button>
                        <button type="button" class="oi-collapse oi-collapse-right" data-oi-collapse="right" aria-label="Sembunyikan form object" title="Sembunyikan / tampilkan form">›</button>
                        <div id="objectForm" class="object-form"><div class="object-empty">Pilih atau tambahkan object informasi.</div></div>
                        <div class="voice-progress oi-toast" id="objectProgress" role="status" aria-live="polite">Data tersimpan ke assets/oi-database.js.</div>
                    </div>
                </div>
                <div class="object-panorama-status"><span id="objectPanoramaStatus">Tarik pin untuk memindahkan WebElement.</span><span><?= count($selectedWebElements) ?> WebElement terdeteksi · Perubahan pano.xml baru diterapkan saat tombol <b>Simpan</b> ditekan.</span></div>
            </div>
            <?php else: ?>
            <div class="skin-notice"><strong>WebElement pano.xml</strong><span><?= count($selectedWebElements) ?> elemen terdeteksi. OI hanya dinilai jika lokasi memiliki WebElement. Data memakai <code>assets/oi-database.js</code> dan <code>assets/oi-config.js</code>.</span></div>
            <div class="object-layout"><aside class="object-list-panel"><input id="objectSearch" type="search" placeholder="Cari object…"><div id="objectCount"></div><div id="objectList"></div></aside><div id="objectForm" class="object-form"><div class="object-empty">Pilih atau tambahkan object informasi.</div></div></div>
            <div class="voice-progress" id="objectProgress">Data tersimpan ke assets/oi-database.js.</div>
            <?php endif; ?>
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
            <div class="section-heading"><div><span class="step">VD</span><h2>Video Mobile & Desktop</h2></div><p>Upload baru akan menimpa video lama. Jika video mobile kosong, tur memakai video desktop dan memotongnya otomatis agar memenuhi layar ponsel.</p></div>
            <select id="videoLocation" hidden><option value="<?= e($selectedId) ?>" data-path="<?= e($detectedTours[$selectedId]['path'] ?? '') ?>" selected><?= e($selectedId) ?></option></select>
            <div class="video-grid">
                <article class="video-card">
                    <div class="video-card-head"><div><strong>Mobile</strong><small id="mobileFileName">video/mobile.webm / .mp4</small></div><span id="mobileStatus" class="media-status">Memeriksa…</span></div>
                    <p class="video-card-note" id="mobileNote" hidden><b>i</b>Versi mobile belum ada. Tur memakai video desktop yang dipotong otomatis agar memenuhi layar ponsel; unggah video mobile bila ingin hasil yang pas.</p>
                    <video id="mobilePreview" controls playsinline></video>
                    <label class="upload-box"><span>Pilih video mobile (WebM/MP4)</span><input type="file" id="mobileFile" accept="video/webm,video/mp4,.webm,.mp4"></label>
                </article>
                <article class="video-card">
                    <div class="video-card-head"><div><strong>Desktop</strong><small id="desktopFileName">video/desktop.webm / .mp4</small></div><span id="desktopStatus" class="media-status">Memeriksa…</span></div>
                    <video id="desktopPreview" controls playsinline></video>
                    <label class="upload-box"><span>Pilih video desktop (WebM/MP4)</span><input type="file" id="desktopFile" accept="video/webm,video/mp4,.webm,.mp4"></label>
                </article>
            </div>
            <div class="voice-progress" id="videoProgress">Pilih satu atau dua video (WebM atau MP4) untuk diunggah.</div>
            <div class="actions"><button type="button" class="save" id="uploadVideo">Upload & Timpa Video</button></div>
        </section>
    <?php elseif ($activeTab === 'inventaris'): $jenis = ($_GET['jenis'] ?? '') === 'video360' ? 'video360' : 'oi'; ?>
        <section class="card section inventaris-editor">
            <div class="section-heading"><div><span class="step">IV</span><div><h2>Inventaris OI & Video 360</h2><p>Daftar semua Object Informasi dan Video 360 beserta tur dan nomornya. Video 360 dibaca dari panorama tur yang memuat video pada <code>pano.xml</code>.</p></div></div><div class="config-heading-actions"><span><?= count($inventoryObjects) ?> OI · <?= count($inventoryVideos) ?> video</span></div></div>
            <nav class="language-section-tabs" aria-label="Jenis inventaris">
                <a class="<?= $jenis === 'oi' ? 'active' : '' ?>" href="?tab=inventaris&jenis=oi">Object Informasi <span><?= count($inventoryObjects) ?></span></a>
                <a class="<?= $jenis === 'video360' ? 'active' : '' ?>" href="?tab=inventaris&jenis=video360">Video 360 <span><?= count($inventoryVideos) ?></span></a>
            </nav>
            <div class="dashboard-table-wrap"><table class="dashboard-table"><thead><tr><th>#</th><th>Tur</th><?php if ($jenis === 'oi'): ?><th>Info ID</th><th>Judul</th><th>Node</th><th>WebElement</th><?php else: ?><th>Panorama</th><th>Berkas video</th><th>Status</th><?php endif; ?></tr></thead><tbody>
            <?php if ($jenis === 'oi'): ?>
                <?php foreach ($inventoryObjects as $row): ?>
                <tr><td><?= (int) $row['no'] ?></td><td><a class="dashboard-tour-link" href="?tab=lokasi&lokasi=<?= e($row['tour']) ?>"><strong><?= e($row['tour_name']) ?></strong><small><?= e($row['tour']) ?></small></a></td><td><code><?= e($row['infoID']) ?></code></td><td><?= e($row['title']) ?></td><td><small><?= e($row['nodeID']) ?></small></td><td><small><?= e($row['targetID']) ?></small></td></tr>
                <?php endforeach; ?>
                <?php if ($inventoryObjects === []): ?><tr><td colspan="6" class="dashboard-empty">Belum ada Object Informasi.</td></tr><?php endif; ?>
            <?php else: ?>
                <?php foreach ($inventoryVideos as $row): ?>
                <tr><td><?= (int) $row['no'] ?></td><td><a class="dashboard-tour-link" href="?tab=lokasi&lokasi=<?= e($row['tour']) ?>"><strong><?= e($row['tour_name']) ?></strong><small><?= e($row['tour']) ?></small></a></td><td><code><?= e($row['node']) ?></code></td><td><small><?= e($row['video']) ?></small></td><td><?= !empty($row['exists']) ? 'Berkas ada' : 'Berkas tidak ditemukan' ?></td></tr>
                <?php endforeach; ?>
                <?php if ($inventoryVideos === []): ?><tr><td colspan="5" class="dashboard-empty">Belum ada panorama ber-video.</td></tr><?php endif; ?>
            <?php endif; ?>
            </tbody></table></div>
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
            <div class="section-heading"><div><span class="step">AU</span><h2>Audit pano.xml</h2></div><p>Memeriksa link kosong dan teks yang belum diterjemahkan. <a href="?tab=inspector&amp;lokasi=<?= e($selectedId) ?>">Inspeksi lengkap di tab Inspector</a></p></div>
            <?php
                // Tur tanpa blok terjemahan Pano2VR: tampilkan kolom bahasa dari registry CMS (en/zh/ar). Blok dibuat otomatis saat disimpan.
                if ($auditResult !== null && ($auditResult['translationUsed'] ?? true) === false) {
                    $virtualLanguages = [];
                    foreach ($languageRegistry as $registryCode => $registryLanguage) if ($registryCode !== 'id') $virtualLanguages[] = (string) ($registryLanguage['pano'] ?? $registryCode);
                    if ($virtualLanguages !== []) {
                        $auditResult['languages'] = $virtualLanguages;
                        $auditResult['displayLanguageIndexes'] = array_keys($virtualLanguages);
                        foreach ($auditResult['translations'] as &$virtualRow) { $virtualRow['values'] = array_fill(0, count($virtualLanguages), ''); $virtualRow['missingCount'] = count($virtualLanguages); }
                        unset($virtualRow);
                        $auditResult['stats']['warnings'] = count($auditResult['translations']) * count($virtualLanguages);
                    }
                }
                if ($auditResult !== null): $stats = $auditResult['stats'];
                    // Memori perubahan: bila pano.xml (mis. hasil export ulang) kehilangan penamaan/terjemahan yang pernah disimpan lewat CMS, tawarkan menerapkannya lagi.
                    $memoryPlan = null;
                    try {
                        $memoryTourDir = $projectRoot . '/' . $detectedTours[$auditId]['path'];
                        $memoryDom = new DOMDocument('1.0', 'UTF-8');
                        if (@$memoryDom->load($memoryTourDir . '/pano.xml')) {
                            panoMemorySeed($memoryTourDir, $memoryDom);
                            $memoryData = panoMemoryLoad($memoryTourDir);
                            $memoryPlan = $memoryData !== null ? panoMemoryPlan($memoryDom, $memoryData) : null;
                            if ($memoryPlan !== null && ($memoryPlan['count'] === 0 || $memoryPlan['hash'] === ($memoryData['ignored'] ?? ''))) $memoryPlan = null;
                        }
                    } catch (Throwable $memoryError) { $memoryPlan = null; }
                ?>
                <?php if ($memoryPlan !== null):
                    $memoryRows = [];
                    foreach ($memoryPlan['translations'] as $item) $memoryRows[] = ['Terjemahan', $item['source'] . ' · ' . $item['code'], '', $item['value']];
                    foreach ($memoryPlan['panoramas'] as $item) $memoryRows[] = ['Panorama', $item['node'] . ' · ' . $item['field'], $item['from'], $item['value']];
                    foreach ($memoryPlan['hotspots'] as $item) $memoryRows[] = ['Hotspot', $item['node'] . '/' . $item['hotspot'] . ' · ' . $item['field'], $item['from'], $item['value']];
                ?>
                <div class="memory-banner" id="panoMemoryBanner" role="status">
                    <div class="memory-banner-head">
                        <span class="memory-icon" aria-hidden="true">&#8635;</span>
                        <div><strong><?= e((string) $memoryPlan['count']) ?> perubahan tersimpan belum ada di pano.xml</strong><small>Biasanya terjadi setelah tur di-export ulang dari Pano2VR. Terjemahan hanya mengisi yang <b>kosong</b>, tidak menimpa terjemahan yang sudah ada.</small></div>
                        <div class="memory-actions"><button type="button" class="save" id="panoMemoryApply">Terapkan ulang (<?= e((string) $memoryPlan['count']) ?>)</button><button type="button" class="pe-link" id="panoMemoryIgnore">Abaikan</button></div>
                    </div>
                    <details><summary>Lihat rincian</summary><ul>
                        <?php foreach (array_slice($memoryRows, 0, 60) as [$kind, $label, $from, $to]): ?><li><b><?= e($kind) ?></b><span><?= e($label) ?></span><em><?= $from !== '' ? e($from) . ' → ' : '' ?><?= e($to) ?></em></li><?php endforeach; ?>
                        <?php if (count($memoryRows) > 60): ?><li><span>…dan <?= count($memoryRows) - 60 ?> lainnya</span></li><?php endif; ?>
                    </ul></details>
                </div>
                <script>
                (() => {
                    const call = async mode => {
                        if (window.cmsPanoEditorDirty) { alert('Simpan dulu perubahan yang sedang diedit di editor pano.xml, lalu coba lagi.'); return; }
                        const buttons = document.querySelectorAll('#panoMemoryBanner button'); buttons.forEach(button => { button.disabled = true; });
                        try {
                            const response = await fetch('api.php?action=pano-memory', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>, id_lokasi:<?= json_encode($auditId) ?>, mode})});
                            const result = await response.json();
                            if (!response.ok || !result.success) throw new Error(result.message || 'Gagal.');
                            const banner = document.getElementById('panoMemoryBanner'); banner.classList.add('is-done'); banner.querySelector('.memory-banner-head strong').textContent = '\u2713 ' + result.message;
                            setTimeout(() => location.reload(), mode === 'apply' ? 900 : 300);
                        } catch (error) { alert('Gagal: ' + error.message); buttons.forEach(button => { button.disabled = false; }); }
                    };
                    document.getElementById('panoMemoryApply')?.addEventListener('click', () => call('apply'));
                    document.getElementById('panoMemoryIgnore')?.addEventListener('click', () => call('ignore'));
                })();
                </script>
                <?php endif; ?>
                <div class="audit-summary"><div><strong><?= e($stats['panoramas']) ?></strong><span>Panorama</span></div><div><strong><?= e($stats['hotspots']) ?></strong><span>Hotspot</span></div><?php $auditErrors = array_values(array_filter($auditResult['issues'], static fn(array $issue): bool => ($issue['level'] ?? '') === 'error')); $linkIssueCount = count(array_filter($auditErrors, static fn(array $issue): bool => ($issue['type'] ?? '') === 'Link kosong')); ?><div class="audit-error"><strong><?= e($linkIssueCount) ?></strong><span>Link belum diisi</span></div><div class="audit-warning"><strong><?= e($stats['warnings']) ?></strong><span>Terjemahan kosong</span></div></div>
                <?php if ($auditErrors !== []):
                    $panoramaTitleByNode = []; foreach ($selectedPanoramas as $panoramaItem) $panoramaTitleByNode[strtolower((string) $panoramaItem['nodeId'])] = (string) ($panoramaItem['title'] !== '' ? $panoramaItem['title'] : $panoramaItem['nodeId']);
                ?>
                <div class="audit-issues" role="alert">
                    <div class="audit-issues-head"><strong><?= count($auditErrors) ?> masalah perlu diperbaiki</strong><span>Klik “Lihat” untuk langsung ke lokasinya.</span></div>
                    <ul>
                    <?php foreach ($auditErrors as $issue):
                        $isLink = ($issue['type'] ?? '') === 'Link kosong';
                        [$issueNode, $issueHotspot] = array_pad(array_map('trim', explode('/', (string) ($issue['location'] ?? ''), 2)), 2, '');
                        $issueTitle = $panoramaTitleByNode[strtolower($issueNode)] ?? '';
                    ?>
                        <li>
                            <span class="audit-issue-type <?= $isLink ? 'is-link' : 'is-other' ?>"><?= e((string) ($issue['type'] ?? 'Masalah')) ?></span>
                            <span class="audit-issue-text">
                                <?php if ($isLink): ?><strong><?= e($issueTitle !== '' ? $issueTitle : $issueNode) ?></strong><small><?= e($issueNode) ?> · hotspot <?= e($issueHotspot) ?></small><?php else: ?><strong><?= e((string) ($issue['location'] ?? '')) ?></strong><?php endif; ?>
                                <em><?= e((string) ($issue['message'] ?? '')) ?></em>
                            </span>
                            <?php if ($isLink): ?><button type="button" class="pe-link" data-audit-jump="hotspot" data-node="<?= e($issueNode) ?>" data-hotspot="<?= e($issueHotspot) ?>">Lihat</button>
                            <?php elseif (($issue['type'] ?? '') === 'Terjemahan'): ?><button type="button" class="pe-link" data-audit-jump="translation">Lihat</button><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
                <script>
                document.addEventListener('click', event => {
                    const button = event.target.closest('[data-audit-jump]'); if (!button) return;
                    const flash = element => { element.classList.add('audit-flash'); setTimeout(() => element.classList.remove('audit-flash'), 2400); };
                    if (button.dataset.auditJump === 'translation') {
                        document.querySelector('[data-pano-editor-tab="translation"]')?.click();
                        const panel = document.querySelector('[data-pano-editor-panel="translation"]'); panel?.scrollIntoView({behavior:'smooth', block:'start'}); if (panel) flash(panel);
                        return;
                    }
                    document.querySelector('[data-pano-editor-tab="hotspot"]')?.click();
                    const group = Array.from(document.querySelectorAll('[data-hotspot-group]')).find(item => item.dataset.hotspotGroup === button.dataset.node);
                    if (!group) return;
                    group.open = true;
                    const card = Array.from(group.querySelectorAll('.pano-edit-card')).find(item => item.dataset.hotspotId === button.dataset.hotspot);
                    const target = card || group;
                    setTimeout(() => { target.scrollIntoView({behavior:'smooth', block:'center'}); flash(target); }, 60);
                });
                </script>
                <?php endif; ?>
                <div class="pano-editor-actions"><strong>Editor pano.xml</strong><span>Field yang belum lengkap langsung ditandai. Backup otomatis dibuat setiap kali menyimpan · <kbd>Ctrl</kbd>+<kbd>S</kbd> untuk simpan.</span><button type="button" class="save" id="savePanoEditor" disabled>Simpan pano.xml</button></div>
                <nav class="pano-editor-tabs"><button type="button" class="active" data-pano-editor-tab="panorama">Panorama <span><?= count($selectedPanoramas) ?></span></button><button type="button" data-pano-editor-tab="hotspot">Hotspot <span><?= count($auditResult['hotspots']) ?></span></button><button type="button" data-pano-editor-tab="translation">Terjemahan <span><?= count($auditResult['translations']) ?></span></button></nav>
                <?php $needsTarget = static fn(array $hotspot): bool => trim((string) ($hotspot['url'] ?? '')) === '' && !str_contains(strtolower((string) ($hotspot['skinId'] ?? '')), '360_video_experience'); ?>
                <?php $missingHotspotCount = count(array_filter($auditResult['hotspots'], $needsTarget)); ?>
                <?php $hotspotsByNode = []; foreach ($auditResult['hotspots'] as $hotspotItem) $hotspotsByNode[strtolower((string) $hotspotItem['nodeId'])][] = $hotspotItem; ?>
                <section class="pano-editor-panel" data-pano-editor-panel="hotspot">
                    <div class="pe-toolbar">
                        <input type="search" class="pe-search" id="hotspotSearch" placeholder="Cari hotspot atau panorama…" aria-label="Cari hotspot">
                        <div class="pe-chips"><button type="button" class="active" data-hotspot-filter="all">Semua</button><button type="button" data-hotspot-filter="missing">Belum ada tujuan <b data-missing-count><?= e($missingHotspotCount) ?></b></button></div>
                        <div class="pe-toolbar-end"><button type="button" class="pe-link" data-hotspot-expand="1">Buka semua</button><button type="button" class="pe-link" data-hotspot-expand="0">Tutup semua</button></div>
                    </div>
                    <div class="pano-hotspot-groups" id="panoHotspotEditor">
                    <?php foreach ($selectedPanoramas as $sourcePanorama): $sourceHotspots = $hotspotsByNode[strtolower((string) $sourcePanorama['nodeId'])] ?? []; $groupMissing = count(array_filter($sourceHotspots, static fn(array $hotspot): bool => trim((string) ($hotspot['url'] ?? '')) === '' && !str_contains(strtolower((string) ($hotspot['skinId'] ?? '')), '360_video_experience'))); ?>
                    <details class="pano-hotspot-group" data-hotspot-group="<?= e($sourcePanorama['nodeId']) ?>" <?= $sourceHotspots !== [] ? '' : 'data-empty' ?>>
                        <summary>
                            <span class="pano-group-thumb" data-preview-node="<?= e($sourcePanorama['nodeId']) ?>" title="Klik untuk preview 360°"><?php if ($sourcePanorama['thumbnail'] !== ''): ?><img src="<?= e($sourcePanorama['thumbnail']) ?>" alt="" loading="lazy"><?php else: ?><i>360°</i><?php endif; ?></span>
                            <span class="pe-group-text"><strong data-group-title><?= e($sourcePanorama['title'] !== '' ? $sourcePanorama['title'] : 'Panorama ' . $sourcePanorama['nodeId']) ?></strong><small data-group-ids><?= e($sourcePanorama['nodeId']) ?><?= $sourcePanorama['customNodeId'] !== '' ? ' · ' . e($sourcePanorama['customNodeId']) : '' ?></small></span>
                            <span class="pe-group-meta"><b class="pe-count"><?= count($sourceHotspots) ?> hotspot</b><b class="pe-warn" data-group-missing <?= $groupMissing === 0 ? 'hidden' : '' ?>><?= $groupMissing ?> belum ada tujuan</b></span>
                            <span class="pe-chevron" aria-hidden="true"></span>
                        </summary>
                        <div class="pano-editor-grid">
                        <?php if ($sourceHotspots === []): ?><div class="pano-group-empty">Panorama ini belum memiliki hotspot.</div><?php endif; ?>
                        <?php foreach ($sourceHotspots as $hotspot):
                            // Jenis tujuan mengikuti skin hotspot:
                            //  - box_ui_location_* (kotak lokasi/kategori) → URL destinasi
                            //  - 360_video_experience_* → video 360 (window.GV360.open)
                            //  - selain itu (hotspot darat, pintu, drone, keluar 360, node) → panorama
                            $skinKey = strtolower((string) $hotspot['skinId']);
                            $hsKind = str_contains($skinKey, '360_video_experience') ? 'video360' : (str_starts_with($skinKey, 'box_ui_location') ? 'url' : 'pano');
                            if ($hsKind === 'pano' && $skinKey === '' && stripos($hotspot['title'], 'est') !== false) $hsKind = 'url';
                            $isEst = $hsKind === 'url';
                            $targetNodeId = trim((string) $hotspot['url'], '{}'); $targetPanorama = null; foreach ($selectedPanoramas as $candidatePanorama) if (strcasecmp($candidatePanorama['nodeId'], $targetNodeId) === 0 || ($candidatePanorama['customNodeId'] !== '' && strcasecmp($candidatePanorama['customNodeId'], $targetNodeId) === 0)) { $targetPanorama = $candidatePanorama; break; }
                            $isPanoTarget = $hotspot['url'] === '' || $targetPanorama !== null;
                            $cardNeedsTarget = $hotspot['url'] === '' && $hsKind !== 'video360';
                            $kindLabel = ['pano' => 'Ke panorama', 'url' => 'Ke URL', 'video360' => 'Video 360'][$hsKind]; ?>
                            <article class="pano-edit-card <?= $cardNeedsTarget ? 'needs-attention' : '' ?> <?= $isEst ? 'est-hotspot' : '' ?>" data-node-id="<?= e($hotspot['nodeId']) ?>" data-hotspot-id="<?= e($hotspot['id']) ?>" data-hs-kind="<?= e($hsKind) ?>">
                                <header><div><strong data-hs-heading><?= e($hotspot['title'] !== '' ? $hotspot['title'] : $hotspot['id']) ?></strong><small title="Skin: <?= e($hotspot['skinId']) ?>">ID <?= e($hotspot['id']) ?> · <?= e($kindLabel) ?></small></div><span class="pe-status" data-hs-status <?= $cardNeedsTarget ? '' : 'hidden' ?>>Belum ada tujuan</span><span class="pe-dirty-badge">Diubah</span><span class="pe-delete-badge">Akan dihapus</span><button type="button" class="pe-hs-delete" data-hs-delete title="Hapus hotspot ini dari pano.xml" aria-label="Hapus hotspot <?= e($hotspot['id']) ?>">Hapus</button><button type="button" class="pe-hs-restore" data-hs-restore>Batal hapus</button></header>
                                <input type="hidden" data-pano-field="deleted" value="">
                                <label class="pe-field"><span>Judul hotspot</span><input data-pano-field="title" value="<?= e($hotspot['title']) ?>" placeholder="mis. Menuju area parkir"></label>
                                <?php if ($hsKind === 'url'): $estUrls = array_map(static fn(string $slug): string => 'https://jakarta.govirtual.id/' . $slug, EST_DESTINATIONS); ?>
                                <label class="pe-field"><span>URL tujuan (destinasi)</span><select data-pano-field="url" data-est-url><option value="">— Pilih destinasi —</option><?php if ($hotspot['url'] !== '' && !in_array($hotspot['url'], $estUrls, true)): ?><option value="<?= e($hotspot['url']) ?>" selected><?= e($hotspot['url']) ?></option><?php endif; ?><?php foreach (EST_DESTINATIONS as $slug): $url = 'https://jakarta.govirtual.id/' . $slug; ?><option value="<?= e($url) ?>" <?= $hotspot['url'] === $url ? 'selected' : '' ?>><?= e(mb_convert_case(str_replace('-', ' ', $slug), MB_CASE_TITLE, 'UTF-8')) ?></option><?php endforeach; ?></select></label>
                                <?php elseif ($hsKind === 'video360'): $videoUrls = array_column($video360Items, 'code'); ?>
                                <label class="pe-field"><span>Video 360 tujuan</span><select data-pano-field="url" data-video360-url><option value="">— Pilih video 360 —</option><?php if ($hotspot['url'] !== '' && !in_array($hotspot['url'], $videoUrls, true)): ?><option value="<?= e($hotspot['url']) ?>" selected>Lainnya: <?= e($hotspot['url']) ?></option><?php endif; ?><?php foreach ($video360Items as $videoId => $videoItem): ?><option value="<?= e($videoItem['code']) ?>" <?= $hotspot['url'] === $videoItem['code'] ? 'selected' : '' ?>><?= e($videoId) ?><?= $videoItem['ready'] ? '' : ' (belum di-encode)' ?></option><?php endforeach; ?></select><small class="pe-hint"><?= $video360Items === [] ? 'Belum ada video 360. Tambahkan dulu di tab 360 Video.' : 'Daftar diambil dari tab 360 Video.' ?></small></label>
                                <?php else: ?>
                                <div class="pe-field pe-target" data-hs-target>
                                    <div class="pe-target-head"><span>Panorama tujuan</span><div class="pe-seg" role="group" aria-label="Jenis tujuan" <?= $isPanoTarget ? 'hidden' : '' ?> title="Tujuan lama bukan panorama; pilih Panorama untuk memperbaikinya"><button type="button" data-hs-mode="pano" class="<?= $isPanoTarget ? 'active' : '' ?>" aria-pressed="<?= $isPanoTarget ? 'true' : 'false' ?>">Panorama</button><button type="button" data-hs-mode="url" class="<?= $isPanoTarget ? '' : 'active' ?>" aria-pressed="<?= $isPanoTarget ? 'false' : 'true' ?>">URL</button></div></div>
                                    <input type="hidden" data-pano-field="url" value="<?= e($hotspot['url']) ?>">
                                    <div class="pe-target-pano" data-hs-pane="pano" <?= $isPanoTarget ? '' : 'hidden' ?>>
                                        <button type="button" class="pe-target-btn" data-hs-open-picker aria-haspopup="dialog"><span class="pe-target-thumb" data-hs-thumb><?php if ($targetPanorama && $targetPanorama['thumbnail'] !== ''): ?><img src="<?= e($targetPanorama['thumbnail']) ?>" alt=""><?php else: ?><i><?= $targetPanorama ? '360°' : '＋' ?></i><?php endif; ?></span><span class="pe-target-label"><strong data-hs-target-title><?= e($targetPanorama ? ($targetPanorama['title'] !== '' ? $targetPanorama['title'] : 'Panorama ' . $targetPanorama['nodeId']) : 'Pilih panorama tujuan') ?></strong><small data-hs-target-node><?= e($targetPanorama ? $targetPanorama['nodeId'] : 'Klik untuk memilih dari daftar') ?></small></span><span class="pe-target-change"><?= $targetPanorama ? 'Ganti' : 'Pilih' ?></span></button>
                                        <select data-hs-target-select hidden aria-hidden="true" tabindex="-1"><option value=""></option><?php foreach ($selectedPanoramas as $targetOption): $targetOptionTitle = $targetOption['title'] !== '' ? $targetOption['title'] : 'Panorama ' . $targetOption['nodeId']; ?><option value="<?= e($targetOption['nodeId']) ?>" data-thumb="<?= e($targetOption['thumbnail']) ?>" data-title="<?= e($targetOptionTitle) ?>" <?= $targetPanorama && $targetPanorama['nodeId'] === $targetOption['nodeId'] ? 'selected' : '' ?>><?= e($targetOptionTitle . ' · ' . $targetOption['nodeId']) ?></option><?php endforeach; ?></select>
                                    </div>
                                    <div class="pe-target-url" data-hs-pane="url" <?= $isPanoTarget ? 'hidden' : '' ?>><input data-hs-url value="<?= e($isPanoTarget ? '' : $hotspot['url']) ?>" placeholder="https://… atau {nodeID}" spellcheck="false"></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($hsKind === 'pano'): ?>
                                <label class="pe-field"><span>Arah pandang saat tiba <em>opsional</em></span><div class="pe-input-action"><input data-pano-field="target" value="<?= e($hotspot['target']) ?>" placeholder="pan/tilt/fov, mis. 35/-1/90" spellcheck="false"><button type="button" data-hs-aim title="Atur arah dengan menggeser panorama tujuan">Atur arah</button></div><small class="pe-hint">Kosongkan untuk memakai arah bawaan panorama tujuan. Klik Atur arah untuk memilih pan/tilt/fov dengan menggeser.</small></label>
                                <?php else: ?>
                                <input type="hidden" data-pano-field="target" value="<?= e($hotspot['target']) ?>">
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                        </div>
                    </details>
                    <?php endforeach; ?>
                    <div class="pe-empty" data-hotspot-empty hidden>Tidak ada hotspot yang cocok.</div>
                    </div>
                </section>
                <section class="pano-editor-panel" data-pano-editor-panel="translation"><div class="pano-filter"><button type="button" class="active" data-translation-filter="all">Semua Terjemahan</button><button type="button" data-translation-filter="missing">Belum Diisi (<?= e($stats['warnings']) ?>)</button><button type="button" class="translate-all" id="translateAllPano" data-state="idle" title="Terjemahkan semua teks yang masih kosong ke bahasa lain"><span class="ta-fill" aria-hidden="true"></span><span class="ta-spinner" aria-hidden="true"></span><span class="ta-star" aria-hidden="true">✦</span><span class="ta-label">Terjemahkan Semua</span></button></div><div class="translation-editor" id="panoTranslationEditor">
                    <?php foreach ($auditResult['translations'] as $translation): $translationMissing = (int) ($translation['missingCount'] ?? 0); ?><article class="translation-row <?= $translationMissing > 0 ? 'needs-translation' : '' ?>" data-source="<?= e($translation['source']) ?>"><div class="translation-source"><strong><?= e($translation['source']) ?></strong><button type="button" class="translation-auto" data-translate-pano title="Auto Translate Kosong" aria-label="Auto Translate Kosong">✦</button></div><?php if ($translationMissing > 0): ?><span class="translation-warning">! <?= $translationMissing ?> bahasa kosong</span><?php endif; ?><div class="translation-fields"><?php foreach ($auditResult['languages'] as $index => $language): $translationValue = (string) ($translation['values'][$index] ?? ''); $isDisplayedLanguage = in_array($index, $auditResult['displayLanguageIndexes'], true); $languageLabel = match (strtolower($language)) { 'en', 'en-us' => 'English', 'zh', 'zh-cn' => 'Mandarin', 'ar', 'ar-sa' => 'Arab', default => $language }; ?><?php if (!$isDisplayedLanguage): ?><textarea hidden data-translation-index="<?= $index ?>"><?= e($translationValue) ?></textarea><?php else: ?><label class="translation-field <?= trim($translationValue) === '' ? 'empty-translation' : '' ?>"><span><?= e($languageLabel) ?></span><textarea rows="1" data-translation-index="<?= $index ?>"><?= e($translationValue) ?></textarea></label><?php endif; ?><?php endforeach; ?></div></article><?php endforeach; ?>
                </div></section>
                <section class="pano-editor-panel active" data-pano-editor-panel="panorama">
                    <div class="pe-toolbar">
                        <input type="search" class="pe-search" id="panoramaSearch" placeholder="Cari judul, node ID, atau custom ID…" aria-label="Cari panorama">
                        <div class="pe-chips"><button type="button" class="active" data-panorama-filter="all">Semua</button><button type="button" data-panorama-filter="untitled">Belum ada judul</button><button type="button" data-panorama-filter="nogeo">Belum ada koordinat</button></div>
                        <div class="pe-toolbar-end"><div class="pe-seg" role="group" aria-label="Tampilan panorama"><button type="button" class="active" data-panorama-view="list" aria-pressed="true">Ringkas</button><button type="button" data-panorama-view="card" aria-pressed="false">Kartu</button></div></div>
                    </div>
                    <div class="panorama-audit-grid is-list" id="panoPanoramaEditor">
                    <?php foreach ($selectedPanoramas as $panorama): $panoramaDomId = 'pe-cid-' . preg_replace('/[^a-z0-9_-]/i', '', (string) $panorama['nodeId']); $panoramaHotspotCount = count($hotspotsByNode[strtolower((string) $panorama['nodeId'])] ?? []); ?>
                    <article class="panorama-audit-card" data-panorama-node-id="<?= e($panorama['nodeId']) ?>">
                        <div class="panorama-audit-thumb"><?php if ($panorama['thumbnail'] !== ''): ?><img src="<?= e($panorama['thumbnail']) ?>" alt="Thumbnail <?= e($panorama['title'] ?: $panorama['nodeId']) ?>" loading="lazy"><?php else: ?><span>Tanpa thumbnail</span><?php endif; ?><span class="pe-dirty-badge">Diubah</span><button type="button" class="pe-thumb-open" data-preview-node="<?= e($panorama['nodeId']) ?>" aria-label="Preview 360° <?= e($panorama['title'] ?: $panorama['nodeId']) ?>" title="Klik untuk preview 360°"><span><b aria-hidden="true">⤢</b><i>Preview 360°</i></span></button></div>
                        <div class="pe-pano-body">
                            <label class="pe-field"><span>Judul panorama</span><input data-panorama-field="title" value="<?= e($panorama['title']) ?>" placeholder="Belum ada judul"></label>
                            <div class="pe-id-row">
                                <div class="pe-field pe-node"><span>Node ID</span><code title="Dibuat oleh Pano2VR, tidak dapat diubah"><?= e($panorama['nodeId']) ?></code></div>
                                <div class="pe-field pe-custom"><label for="<?= e($panoramaDomId) ?>"><span>Custom Node ID <em>opsional</em></span></label><div class="pe-input-action"><input id="<?= e($panoramaDomId) ?>" data-panorama-field="customNodeId" value="<?= e($panorama['customNodeId']) ?>" placeholder="opsional, mis. pintu-masuk" autocomplete="off" spellcheck="false" maxlength="100"><button type="button" data-custom-from-title title="Isi otomatis dari judul panorama">Dari judul</button></div><small class="pe-hint" data-custom-id-hint></small></div>
                            </div>
                            <div class="pe-geo">
                                <label class="pe-field"><span>Latitude</span><input type="number" step="any" min="-90" max="90" data-panorama-field="latitude" value="<?= e($panorama['latitude']) ?>" placeholder="-6.175392"></label>
                                <label class="pe-field"><span>Longitude</span><input type="number" step="any" min="-180" max="180" data-panorama-field="longitude" value="<?= e($panorama['longitude']) ?>" placeholder="106.827153"></label>
                                <div class="panorama-geo-actions"><button type="button" data-set-panorama-geo title="Pakai lokasi saya"><span aria-hidden="true">⌖</span><span class="pe-btn-label">Pakai lokasi saya</span></button><a data-open-panorama-map href="<?= $panorama['latitude'] !== '' && $panorama['longitude'] !== '' ? 'https://www.openstreetmap.org/?mlat=' . rawurlencode($panorama['latitude']) . '&mlon=' . rawurlencode($panorama['longitude']) . '#map=17/' . rawurlencode($panorama['latitude']) . '/' . rawurlencode($panorama['longitude']) : '#' ?>" target="_blank" rel="noopener" <?= $panorama['latitude'] === '' || $panorama['longitude'] === '' ? 'hidden' : '' ?> title="Buka peta"><span class="pe-btn-label">Buka peta </span><span aria-hidden="true">↗</span></a></div>
                            </div>
                            <footer class="pe-pano-foot"><button type="button" class="pe-link" data-see-panorama-hotspots="<?= e($panorama['nodeId']) ?>" <?= $panoramaHotspotCount === 0 ? 'disabled' : '' ?>><?= $panoramaHotspotCount === 0 ? '0 hotspot' : '<span class="pe-btn-label">Lihat </span>' . $panoramaHotspotCount . ' hotspot →' ?></button></footer>
                        </div>
                    </article>
                    <?php endforeach; ?>
                    <div class="pe-empty" data-panorama-empty hidden>Tidak ada panorama yang cocok.</div>
                    </div>
                </section>
                <div class="pe-savebar" id="panoSaveBar" role="status" aria-live="polite"><span class="pe-savebar-dot" aria-hidden="true"></span><span id="panoEditorProgress">Tidak ada perubahan</span><button type="button" class="pe-ghost" id="panoDiscard">Batalkan</button><button type="button" class="save" id="panoSaveBottom" disabled>Simpan pano.xml</button></div>
                <div class="pe-picker" id="panoTargetPicker" hidden role="dialog" aria-modal="true" aria-labelledby="panoTargetPickerTitle">
                    <div class="pe-picker-backdrop" data-picker-close></div>
                    <div class="pe-picker-dialog">
                        <header><div><strong id="panoTargetPickerTitle">Pilih panorama tujuan</strong><small id="panoTargetPickerFor"></small></div><button type="button" class="pe-picker-x" data-picker-close aria-label="Tutup">×</button></header>
                        <div class="pe-picker-tools"><input type="search" class="pe-search" id="panoTargetPickerSearch" placeholder="Cari judul, node ID, atau custom ID…" aria-label="Cari panorama tujuan"></div>
                        <div class="pe-picker-grid" id="panoTargetPickerGrid">
                            <?php foreach ($selectedPanoramas as $pickerPanorama): $pickerTitle = $pickerPanorama['title'] !== '' ? $pickerPanorama['title'] : 'Panorama ' . $pickerPanorama['nodeId']; ?>
                            <button type="button" class="pe-picker-item" data-pick-node="<?= e($pickerPanorama['nodeId']) ?>">
                                <span class="pe-picker-thumb"><?php if ($pickerPanorama['thumbnail'] !== ''): ?><img src="<?= e($pickerPanorama['thumbnail']) ?>" alt="" loading="lazy"><?php else: ?><i>360°</i><?php endif; ?><em class="pe-picker-tag" data-picker-tag hidden></em></span>
                                <span class="pe-picker-meta"><strong data-picker-title><?= e($pickerTitle) ?></strong><small data-picker-ids><?= e($pickerPanorama['nodeId']) ?><?= $pickerPanorama['customNodeId'] !== '' ? ' · ' . e($pickerPanorama['customNodeId']) : '' ?></small></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="pe-empty" data-picker-empty hidden>Tidak ada panorama yang cocok.</div>
                        <footer><button type="button" class="pe-link" data-pick-node="">Kosongkan tujuan</button><span>Klik thumbnail untuk memilih · <kbd>Esc</kbd> untuk menutup</span></footer>
                    </div>
                </div>
                <div class="pe-preview" id="panoPreview" hidden role="dialog" aria-modal="true" aria-labelledby="panoPreviewTitle">
                    <div class="pe-picker-backdrop" data-preview-close></div>
                    <div class="pe-preview-dialog">
                        <header>
                            <div class="pe-preview-heading"><strong id="panoPreviewTitle"></strong><small id="panoPreviewMeta"></small></div>
                            <span class="pe-preview-count" id="panoPreviewCount"></span>
                            <a class="pe-preview-open" id="panoPreviewOpen" href="#" target="_blank" rel="noopener">Buka di tur ↗</a>
                            <button type="button" class="pe-picker-x" data-preview-close aria-label="Tutup preview">×</button>
                        </header>
                        <div class="pe-preview-stage">
                            <img id="panoPreviewImage" alt="">
                            <iframe id="panoPreviewFrame" title="Preview 360° panorama" allow="fullscreen; gyroscope; accelerometer"></iframe>
                            <span class="pe-preview-status" id="panoPreviewStatus">Memuat 360°…</span>
                            <button type="button" class="pe-preview-nav is-prev" data-preview-step="-1" aria-label="Panorama sebelumnya">‹</button>
                            <button type="button" class="pe-preview-nav is-next" data-preview-step="1" aria-label="Panorama berikutnya">›</button>
                            <span class="pe-preview-hint">Seret untuk melihat sekeliling · <kbd>←</kbd> <kbd>→</kbd> pindah panorama</span>
                        </div>
                        <footer>
                            <label><span>Judul panorama</span><input id="panoPreviewTitleInput" placeholder="Beri judul agar mudah dikenali" autocomplete="off"></label>
                            <button type="button" class="pe-preview-hotspots" id="panoPreviewHotspots">Lihat hotspot →</button>
                            <div class="pe-preview-aim" id="panoAimBar" hidden>
                                <span class="pe-preview-aim-live" id="panoAimLive">Geser panorama untuk menentukan arah.</span>
                                <?php if ($tourSetup !== null): ?>
                                <button type="button" class="pe-preview-hotspots" id="panoAimSetup" title="Simpan arah langsung ke pano.xml, lalu <?= e(mb_strtolower($tourSetup['title'])) ?>">Simpan arah</button>
                                <?php else: ?>
                                <button type="button" class="pe-preview-hotspots" id="panoAimSave">Simpan arah</button>
                                <?php endif; ?>
                                <button type="button" class="pe-preview-hotspots" id="panoAimCancel">Batal</button>
                            </div>
                        </footer>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
    <?php if (!in_array($activeTab, ['dashboard', 'languages', 'translations', 'custom', 'video360', 'inventaris', 'smart', 'aihub', 'inspector', 'loading'], true)): ?>
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
// form aksi (mis. Template Skin: lokasi yang perlu sudah tercentang sejak awal) tidak ikut penjaga "perubahan belum disimpan";
// tanpa pengecualian ini tombol Terapkan nonaktif karena isi form dianggap "tidak berubah"
const guardedEditorForms = Array.from(document.querySelectorAll('form.editor:not([data-action-form])'));
let allowEditorNavigation = false;
const editorFormState = form => JSON.stringify(Array.from(form.elements).filter(field => field.name && !['submit', 'button'].includes(field.type)).map(field => [field.name, ['checkbox', 'radio'].includes(field.type) ? field.checked : field.value]));
const initialEditorStates = new Map(guardedEditorForms.map(form => [form, editorFormState(form)]));
const hasUnsavedEditorChanges = () => window.cmsPanoEditorDirty === true || guardedEditorForms.some(form => editorFormState(form) !== initialEditorStates.get(form));
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
    form.addEventListener('submit', event => {
        allowEditorNavigation = true;
        const submitter = event.submitter;
        saveButtons.forEach(button => {
            if (button.disabled) return;
            button.dataset.label = button.textContent;
            button.textContent = 'Menyimpan…';
            if (button === submitter) {
                // Submitter harus tetap enabled sampai browser membentuk FormData;
                // jika disabled, name="action" hilang dan backend mengira save_location.
                button.setAttribute('aria-disabled', 'true');
                button.style.pointerEvents = 'none';
            } else {
                button.disabled = true;
            }
        });
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
    const auditEditor = document.querySelector('.audit-editor');
    const hotspotEditor = document.getElementById('panoHotspotEditor');
    const panoramaEditor = document.getElementById('panoPanoramaEditor');
    const panoProgress = document.getElementById('panoEditorProgress');
    const saveBar = document.getElementById('panoSaveBar');
    const saveBottom = document.getElementById('panoSaveBottom');
    document.body.appendChild(saveBar); // keluar dari kartu agar position:fixed tidak terkunci oleh transform induk
    const hotspotCards = () => Array.from(document.querySelectorAll('.pano-edit-card'));
    const panoramaCards = () => Array.from(document.querySelectorAll('[data-panorama-node-id]'));
    const translationRows = () => Array.from(document.querySelectorAll('.translation-row'));
    const groups = () => Array.from(document.querySelectorAll('.pano-hotspot-group'));
    const lower = value => String(value || '').trim().toLowerCase();

    // --- Tab
    const showEditorTab = name => {
        document.querySelectorAll('[data-pano-editor-tab], [data-pano-editor-panel]').forEach(item=>item.classList.remove('active'));
        document.querySelector('[data-pano-editor-tab="'+name+'"]')?.classList.add('active');
        document.querySelector('[data-pano-editor-panel="'+name+'"]')?.classList.add('active');
    };
    document.querySelectorAll('[data-pano-editor-tab]').forEach(button => button.addEventListener('click', () => showEditorTab(button.dataset.panoEditorTab)));

    // --- Status bar & penanda perubahan
    let saving = false, dirtyCount = 0, hideTimer = 0, dirtyFrame = 0;
    const flash = (message, tone = '') => { clearTimeout(hideTimer); panoProgress.textContent = message; saveBar.dataset.tone = tone; saveBar.classList.add('visible'); };
    const readHotspot = card => ({nodeId:card.dataset.nodeId,id:card.dataset.hotspotId,deleted:card.querySelector('[data-pano-field="deleted"]')?.value === '1',title:card.querySelector('[data-pano-field="title"]').value,url:card.querySelector('[data-pano-field="url"]').value,target:card.querySelector('[data-pano-field="target"]').value});
    const readPanorama = card => ({nodeId:card.dataset.panoramaNodeId,title:card.querySelector('[data-panorama-field="title"]').value,customNodeId:card.querySelector('[data-panorama-field="customNodeId"]').value,latitude:card.querySelector('[data-panorama-field="latitude"]').value,longitude:card.querySelector('[data-panorama-field="longitude"]').value});
    const readTranslation = row => Array.from(row.querySelectorAll('[data-translation-index]')).map(field => field.value);
    const itemKey = el => el.dataset.hotspotId !== undefined ? 'h:'+el.dataset.nodeId+'/'+el.dataset.hotspotId : el.dataset.panoramaNodeId !== undefined ? 'p:'+el.dataset.panoramaNodeId : 't:'+el.dataset.source;
    const itemValue = el => JSON.stringify(el.classList.contains('pano-edit-card') ? readHotspot(el) : el.dataset.panoramaNodeId !== undefined ? readPanorama(el) : readTranslation(el));
    const trackedItems = () => [...hotspotCards(), ...panoramaCards(), ...translationRows()];
    let baseline = new Map();
    const takeBaseline = () => { baseline = new Map(trackedItems().map(el => [itemKey(el), itemValue(el)])); };
    const refreshDirty = () => {
        dirtyCount = 0;
        trackedItems().forEach(el => { const changed = baseline.get(itemKey(el)) !== itemValue(el); el.classList.toggle('is-dirty', changed); if (changed) dirtyCount++; });
        window.cmsPanoEditorDirty = dirtyCount > 0;
        if (saving) return;
        panoSaveButton.disabled = saveBottom.disabled = dirtyCount === 0;
        panoProgress.textContent = dirtyCount ? dirtyCount + ' perubahan belum disimpan' : 'Tidak ada perubahan';
        saveBar.dataset.tone = dirtyCount ? 'dirty' : '';
        saveBar.classList.toggle('visible', dirtyCount > 0);
    };
    const scheduleDirty = () => { cancelAnimationFrame(dirtyFrame); dirtyFrame = requestAnimationFrame(refreshDirty); };
    auditEditor.addEventListener('input', scheduleDirty);
    auditEditor.addEventListener('change', scheduleDirty);

    // --- Hotspot: status tujuan & jumlah per grup
    const refreshHotspotState = card => {
        // Hotspot video 360 memang tanpa URL tujuan → jangan ditandai kurang.
        const missing = !card.classList.contains('is-deleted') && card.dataset.hsKind !== 'video360' && card.querySelector('[data-pano-field="url"]').value.trim() === '';
        card.classList.toggle('needs-attention', missing);
        const status = card.querySelector('[data-hs-status]'); if (status) status.hidden = !missing;
    };
    const refreshGroupCounts = () => {
        let total = 0;
        groups().forEach(group => {
            const missing = group.querySelectorAll('.pano-edit-card.needs-attention').length; total += missing;
            const badge = group.querySelector('[data-group-missing]'); if (badge) { badge.hidden = missing === 0; badge.textContent = missing + ' belum ada tujuan'; }
        });
        document.querySelectorAll('[data-missing-count]').forEach(item => { item.textContent = total; });
    };
    const setTargetUrl = (target, value) => {
        const card = target.closest('.pano-edit-card'), hidden = target.querySelector('[data-pano-field="url"]');
        hidden.value = value; refreshHotspotState(card); refreshGroupCounts(); scheduleDirty();
    };
    const updateTargetThumb = target => {
        const select = target.querySelector('[data-hs-target-select]'), holder = target.querySelector('[data-hs-thumb]'), option = select.selectedOptions[0];
        const thumb = option && option.value ? option.dataset.thumb : '';
        holder.innerHTML = thumb ? '<img src="'+escapeHtml(thumb)+'" alt="">' : '<i>'+(option && option.value ? '360°' : '＋')+'</i>';
        const picked = !!(option && option.value);
        target.querySelector('[data-hs-target-title]').textContent = picked ? option.dataset.title : 'Pilih panorama tujuan';
        target.querySelector('[data-hs-target-node]').textContent = picked ? option.value : 'Klik untuk memilih dari daftar';
        target.querySelector('.pe-target-change').textContent = picked ? 'Ganti' : 'Pilih';
    };

    // --- Popup pemilih panorama tujuan (satu dialog untuk semua hotspot)
    const picker = document.getElementById('panoTargetPicker');
    document.body.appendChild(picker);
    const pickerSearch = document.getElementById('panoTargetPickerSearch');
    const pickerItems = () => Array.from(picker.querySelectorAll('.pe-picker-item'));
    let pickerTarget = null;
    const filterPicker = () => {
        const query = lower(pickerSearch.value); let shown = 0;
        pickerItems().forEach(item => { const show = !query || lower(item.textContent).includes(query); item.hidden = !show; if (show) shown++; });
        picker.querySelector('[data-picker-empty]').hidden = shown > 0;
    };
    const closePicker = () => { picker.hidden = true; document.body.classList.remove('modal-open'); const button = pickerTarget?.querySelector('[data-hs-open-picker]'); pickerTarget = null; button?.focus(); };
    const openPicker = target => {
        pickerTarget = target;
        const card = target.closest('.pano-edit-card'), current = target.querySelector('[data-hs-target-select]').value, source = card.dataset.nodeId;
        const groupTitle = card.closest('.pano-hotspot-group')?.querySelector('[data-group-title]')?.textContent || source;
        document.getElementById('panoTargetPickerFor').textContent = 'Hotspot “' + card.querySelector('[data-hs-heading]').textContent + '” di ' + groupTitle;
        pickerItems().forEach(item => {
            const node = item.dataset.pickNode, isCurrent = node === current, isSource = lower(node) === lower(source), tag = item.querySelector('[data-picker-tag]');
            item.classList.toggle('is-current', isCurrent); item.setAttribute('aria-pressed', isCurrent ? 'true' : 'false');
            tag.hidden = !isCurrent && !isSource; tag.textContent = isCurrent ? '✓ Terpilih' : 'Panorama asal';
            tag.classList.toggle('is-source', !isCurrent && isSource);
        });
        pickerSearch.value = ''; filterPicker();
        picker.hidden = false; document.body.classList.add('modal-open');
        (picker.querySelector('.pe-picker-item.is-current') || pickerSearch).scrollIntoView({block:'nearest'});
        pickerSearch.focus({preventScroll:true});
    };
    hotspotEditor.addEventListener('click', event => { const button = event.target.closest('[data-hs-open-picker]'); if (button) openPicker(button.closest('[data-hs-target]')); });
    picker.addEventListener('click', event => {
        if (event.target.closest('[data-picker-close]')) { closePicker(); return; }
        const item = event.target.closest('[data-pick-node]'); if (!item || !pickerTarget) return;
        const select = pickerTarget.querySelector('[data-hs-target-select]');
        select.value = item.dataset.pickNode; select.dispatchEvent(new Event('change', {bubbles:true}));
        closePicker();
    });
    pickerSearch.addEventListener('input', filterPicker);
    pickerSearch.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); pickerItems().find(item => !item.hidden)?.click(); } });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !picker.hidden) closePicker(); });
    const setTargetMode = (target, mode) => {
        target.querySelectorAll('[data-hs-mode]').forEach(button => { const active = button.dataset.hsMode === mode; button.classList.toggle('active', active); button.setAttribute('aria-pressed', active ? 'true' : 'false'); });
        target.querySelectorAll('[data-hs-pane]').forEach(pane => { pane.hidden = pane.dataset.hsPane !== mode; });
        if (mode === 'pano') { const value = target.querySelector('[data-hs-target-select]').value; setTargetUrl(target, value ? '{'+value+'}' : ''); }
        else { const input = target.querySelector('[data-hs-url]'); setTargetUrl(target, input.value.trim()); input.focus(); }
    };
    hotspotEditor.addEventListener('click', event => {
        const modeButton = event.target.closest('[data-hs-mode]');
        if (modeButton && !modeButton.classList.contains('active')) setTargetMode(modeButton.closest('[data-hs-target]'), modeButton.dataset.hsMode);
    });
    hotspotEditor.addEventListener('change', event => {
        if (event.target.matches('[data-hs-target-select]')) { const target = event.target.closest('[data-hs-target]'); setTargetUrl(target, event.target.value ? '{'+event.target.value+'}' : ''); updateTargetThumb(target); }
        if (event.target.matches('[data-est-url], [data-video360-url]')) { refreshHotspotState(event.target.closest('.pano-edit-card')); refreshGroupCounts(); }
    });
    hotspotEditor.addEventListener('input', event => {
        if (event.target.matches('[data-hs-url]')) setTargetUrl(event.target.closest('[data-hs-target]'), event.target.value.trim());
        if (event.target.dataset.panoField === 'title') {
            const card = event.target.closest('.pano-edit-card');
            card.querySelector('[data-hs-heading]').textContent = event.target.value.trim() || card.dataset.hotspotId;
            if (/\best\.?\b/i.test(event.target.value)) { const url=card.querySelector('[data-est-url]');if(url&&!url.value.trim()){const match=findEstDestination(event.target.value);if(match){url.value=match;refreshHotspotState(card);refreshGroupCounts();}} }
        }
    });

    // --- Hotspot: tandai hapus / batal hapus (baru benar-benar dihapus saat Simpan)
    const setHotspotDeleted = (card, deleted) => {
        card.querySelector('[data-pano-field="deleted"]').value = deleted ? '1' : '';
        card.classList.toggle('is-deleted', deleted);
        card.querySelectorAll('input:not([type="hidden"]), select, textarea, .pe-target-btn, [data-hs-mode]').forEach(field => { field.disabled = deleted; });
        refreshHotspotState(card); refreshGroupCounts(); scheduleDirty();
    };
    hotspotEditor.addEventListener('click', event => {
        const del = event.target.closest('[data-hs-delete]'), restore = event.target.closest('[data-hs-restore]');
        if (del) { const card = del.closest('.pano-edit-card'); if (confirm('Hapus hotspot “' + card.querySelector('[data-hs-heading]').textContent + '” (' + card.dataset.hotspotId + ')? Hotspot benar-benar dihapus dari pano.xml setelah Anda menekan Simpan.')) setHotspotDeleted(card, true); }
        if (restore) setHotspotDeleted(restore.closest('.pano-edit-card'), false);
    });
    const removeDeletedHotspots = () => {
        document.querySelectorAll('.pano-edit-card.is-deleted').forEach(card => {
            const group = card.closest('.pano-hotspot-group'); card.remove();
            if (!group) return;
            const left = group.querySelectorAll('.pano-edit-card').length, count = group.querySelector('.pe-count');
            if (count) count.textContent = left + ' hotspot';
            if (left === 0) { group.dataset.empty = ''; group.querySelector('.pano-editor-grid').insertAdjacentHTML('afterbegin', '<div class="pano-group-empty">Panorama ini belum memiliki hotspot.</div>'); }
        });
        const tabCount = document.querySelector('[data-pano-editor-tab="hotspot"] span'); if (tabCount) tabCount.textContent = hotspotCards().length;
        refreshGroupCounts();
    };

    // --- Hotspot: pencarian, filter, buka/tutup semua
    let hotspotFilter = 'all';
    const hotspotSearch = document.getElementById('hotspotSearch');
    const applyHotspotView = () => {
        const query = lower(hotspotSearch.value), filtering = query !== '' || hotspotFilter !== 'all';
        let shownGroups = 0;
        groups().forEach(group => {
            const groupMatch = !query || lower(group.querySelector('summary').textContent).includes(query);
            let visible = 0;
            group.querySelectorAll('.pano-edit-card').forEach(card => {
                const text = lower(card.querySelector('[data-pano-field="title"]').value + ' ' + card.dataset.hotspotId + ' ' + card.querySelector('[data-pano-field="url"]').value);
                const show = (hotspotFilter === 'all' || card.classList.contains('needs-attention')) && (groupMatch || text.includes(query));
                card.hidden = !show; if (show) visible++;
            });
            group.hidden = filtering && visible === 0 && !(hotspotFilter === 'all' && groupMatch);
            if (filtering && visible > 0) group.open = true;
            if (!group.hidden) shownGroups++;
        });
        document.querySelector('[data-hotspot-empty]').hidden = shownGroups > 0;
    };
    hotspotSearch.addEventListener('input', applyHotspotView);
    document.querySelectorAll('[data-hotspot-filter]').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('[data-hotspot-filter]').forEach(item => item.classList.toggle('active', item === button));
        hotspotFilter = button.dataset.hotspotFilter; applyHotspotView();
    }));
    document.querySelectorAll('[data-hotspot-expand]').forEach(button => button.addEventListener('click', () => {
        groups().forEach(group => { if (!group.hidden && !group.hasAttribute('data-empty')) group.open = button.dataset.hotspotExpand === '1'; });
    }));

    // --- Panorama: pencarian & filter
    let panoramaFilter = 'all';
    const panoramaSearch = document.getElementById('panoramaSearch');
    const applyPanoramaView = () => {
        const query = lower(panoramaSearch.value); let shown = 0;
        panoramaCards().forEach(card => {
            const data = readPanorama(card), text = lower(data.title + ' ' + data.nodeId + ' ' + data.customNodeId);
            const filterOk = panoramaFilter === 'all' || (panoramaFilter === 'untitled' && !data.title.trim()) || (panoramaFilter === 'nogeo' && (!data.latitude.trim() || !data.longitude.trim()));
            card.hidden = !(filterOk && (!query || text.includes(query))); if (!card.hidden) shown++;
        });
        document.querySelector('[data-panorama-empty]').hidden = shown > 0;
    };
    panoramaSearch.addEventListener('input', applyPanoramaView);
    // Tampilan Ringkas (daftar) / Kartu — diingat per browser
    const setPanoramaView = view => {
        panoramaEditor.classList.toggle('is-list', view !== 'card');
        document.querySelectorAll('[data-panorama-view]').forEach(button => { const active = button.dataset.panoramaView === (view === 'card' ? 'card' : 'list'); button.classList.toggle('active', active); button.setAttribute('aria-pressed', active ? 'true' : 'false'); });
        try { localStorage.setItem('cmsPanoramaView', view === 'card' ? 'card' : 'list'); } catch (_) {}
    };
    document.querySelectorAll('[data-panorama-view]').forEach(button => button.addEventListener('click', () => setPanoramaView(button.dataset.panoramaView)));
    try { if (localStorage.getItem('cmsPanoramaView') === 'card') setPanoramaView('card'); } catch (_) {}
    document.querySelectorAll('[data-panorama-filter]').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('[data-panorama-filter]').forEach(item => item.classList.toggle('active', item === button));
        panoramaFilter = button.dataset.panoramaFilter; applyPanoramaView();
    }));

    // --- Panorama: Custom Node ID (validasi langsung + isi dari judul)
    const CUSTOM_ID_PATTERN = /^[A-Za-z0-9_-]{1,100}$/;
    const validateCustomIds = () => {
        const cards = panoramaCards(), used = new Map(), nodeIds = new Set(cards.map(card => lower(card.dataset.panoramaNodeId)));
        cards.forEach(card => { const value = lower(card.querySelector('[data-panorama-field="customNodeId"]').value); if (value) used.set(value, (used.get(value) || 0) + 1); });
        let invalid = 0;
        cards.forEach(card => {
            const input = card.querySelector('[data-panorama-field="customNodeId"]'), hint = card.querySelector('[data-custom-id-hint]'), value = input.value.trim(), key = lower(value);
            let message = 'Huruf, angka, tanda - dan _. Harus unik.', state = '';
            if (value && !CUSTOM_ID_PATTERN.test(value)) { message = 'Hanya huruf, angka, tanda - dan _ (tanpa spasi).'; state = 'error'; }
            else if (value && used.get(key) > 1) { message = 'ID ini dipakai lebih dari satu panorama.'; state = 'error'; }
            else if (value && nodeIds.has(key) && key !== lower(card.dataset.panoramaNodeId)) { message = 'Sama dengan Node ID panorama lain.'; state = 'error'; }
            else if (value) { message = 'ID valid dan unik.'; state = 'ok'; }
            hint.textContent = message; hint.dataset.state = state;
            input.setAttribute('aria-invalid', state === 'error' ? 'true' : 'false');
            card.classList.toggle('has-error', state === 'error'); if (state === 'error') invalid++;
        });
        return invalid;
    };
    const slugify = value => String(value || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 60).replace(/-+$/, '');
    const syncPanoramaLabels = card => {
        const data = readPanorama(card), title = data.title.trim() || 'Panorama ' + data.nodeId;
        const group = document.querySelector('[data-hotspot-group="'+CSS.escape(data.nodeId)+'"]');
        if (group) { group.querySelector('[data-group-title]').textContent = title; group.querySelector('[data-group-ids]').textContent = data.nodeId + (data.customNodeId.trim() ? ' · ' + data.customNodeId.trim() : ''); }
        document.querySelectorAll('[data-hs-target-select] option[value="'+CSS.escape(data.nodeId)+'"]').forEach(option => {
            option.textContent = title + ' · ' + data.nodeId; option.dataset.title = title;
            if (option.selected) option.closest('[data-hs-target]').querySelector('[data-hs-target-title]').textContent = title;
        });
        const pickerItem = picker.querySelector('[data-pick-node="'+CSS.escape(data.nodeId)+'"]');
        if (pickerItem) { pickerItem.querySelector('[data-picker-title]').textContent = title; pickerItem.querySelector('[data-picker-ids]').textContent = data.nodeId + (data.customNodeId.trim() ? ' · ' + data.customNodeId.trim() : ''); }
    };
    const updatePanoramaMapLink = card => { const lat=card.querySelector('[data-panorama-field="latitude"]').value.trim(),lng=card.querySelector('[data-panorama-field="longitude"]').value.trim(),link=card.querySelector('[data-open-panorama-map]');if(!link)return;if(lat&&lng){link.href='https://www.openstreetmap.org/?mlat='+encodeURIComponent(lat)+'&mlon='+encodeURIComponent(lng)+'#map=17/'+encodeURIComponent(lat)+'/'+encodeURIComponent(lng);link.hidden=false;}else{link.href='#';link.hidden=true;} };
    panoramaEditor.addEventListener('input', event => {
        const card = event.target.closest('[data-panorama-node-id]'); if (!card) return;
        if (event.target.matches('[data-panorama-field="customNodeId"]')) validateCustomIds();
        if (event.target.matches('[data-panorama-field="title"],[data-panorama-field="customNodeId"]')) syncPanoramaLabels(card);
        if (event.target.matches('[data-panorama-field="latitude"],[data-panorama-field="longitude"]')) updatePanoramaMapLink(card);
    });
    panoramaEditor.addEventListener('click', event => {
        const card = event.target.closest('[data-panorama-node-id]'); if (!card) return;
        const fromTitle = event.target.closest('[data-custom-from-title]');
        if (fromTitle) {
            const titleInput = card.querySelector('[data-panorama-field="title"]'), input = card.querySelector('[data-panorama-field="customNodeId"]');
            const base = slugify(titleInput.value);
            if (!base) { flash('Isi judul panorama dulu, lalu klik "Dari judul".', 'error'); titleInput.focus(); return; }
            const taken = new Set(panoramaCards().filter(other => other !== card).flatMap(other => [lower(other.querySelector('[data-panorama-field="customNodeId"]').value), lower(other.dataset.panoramaNodeId)]));
            let candidate = base, suffix = 2; while (taken.has(candidate)) candidate = base + '-' + suffix++;
            input.value = candidate; input.dispatchEvent(new Event('input', {bubbles:true})); input.focus();
            return;
        }
        const seeHotspots = event.target.closest('[data-see-panorama-hotspots]');
        if (seeHotspots) {
            showEditorTab('hotspot');
            const group = document.querySelector('[data-hotspot-group="'+CSS.escape(seeHotspots.dataset.seePanoramaHotspots)+'"]');
            if (group) { group.hidden = false; group.open = true; group.scrollIntoView({behavior:'smooth', block:'start'}); }
            return;
        }
        const geoButton = event.target.closest('[data-set-panorama-geo]');
        if (geoButton) {
            if (!navigator.geolocation) { flash('Browser tidak mendukung geolocation.', 'error'); return; }
            const geoLabel = geoButton.querySelector('.pe-btn-label'); geoButton.disabled = true; geoLabel.textContent = 'Mencari lokasi…';
            navigator.geolocation.getCurrentPosition(position => {
                card.querySelector('[data-panorama-field="latitude"]').value = position.coords.latitude.toFixed(7);
                card.querySelector('[data-panorama-field="longitude"]').value = position.coords.longitude.toFixed(7);
                updatePanoramaMapLink(card); geoButton.disabled = false; geoLabel.textContent = 'Pakai lokasi saya'; refreshDirty();
            }, error => { geoButton.disabled = false; geoLabel.textContent = 'Pakai lokasi saya'; flash('Lokasi gagal diambil: ' + error.message, 'error'); }, {enableHighAccuracy:true, timeout:10000, maximumAge:0});
        }
    });

    // --- Preview 360° panorama (klik thumbnail)
    const preview = document.getElementById('panoPreview');
    document.body.appendChild(preview);
    const previewFrame = document.getElementById('panoPreviewFrame');
    const previewImage = document.getElementById('panoPreviewImage');
    const previewStatus = document.getElementById('panoPreviewStatus');
    const previewTitleInput = document.getElementById('panoPreviewTitleInput');
    const previewTourUrl = <?= json_encode(isset($detectedTours[$selectedId]) ? projectUrl($detectedTours[$selectedId]['path'] . '/index.html') : '', JSON_UNESCAPED_SLASHES) ?>;
    let previewCard = null, previewFrameState = 'idle', previewReadyTimer = 0;
    const previewList = () => { const visible = panoramaCards().filter(card => !card.hidden); return visible.length ? visible : panoramaCards(); };
    const previewPlayer = () => { try { return previewFrame.contentWindow?.pano || null; } catch (_) { return null; } };
    // Sembunyikan skin, layar pembuka, dan suara tur di dalam preview.
    const quietPreviewFrame = () => {
        // Mode atur arah: hanya lapisan penutup yang dibuang; kontrol mouse Pano2VR (divSkin) tetap hidup.
        const aiming = preview.dataset.mode === 'aim';
        try {
            const doc = previewFrame.contentDocument, player = previewPlayer();
            doc?.querySelectorAll('[id$="skin-overlay"],[id$="skin-boot-cover"]').forEach(element => element.remove());
            if (!aiming && player?.divSkin) { player.divSkin.style.display = 'none'; player.divSkin.style.pointerEvents = 'none'; }
            const skin = previewFrame.contentWindow?.skin; if (skin?.divSkin) skin.divSkin.style.display = 'none';
            player?.mute?.('_all');
            doc?.querySelectorAll('audio').forEach(media => { media.muted = true; });
        } catch (_) {}
    };
    const showPreviewReady = () => {
        clearTimeout(previewReadyTimer);
        previewReadyTimer = setTimeout(() => { previewFrame.classList.add('ready'); previewStatus.hidden = true; }, 700);
    };
    const openNodeInPreview = node => {
        const player = previewPlayer(); if (!player) return;
        try { player.openNext('{' + node + '}'); player.setLocked?.(false); } catch (_) {}
        quietPreviewFrame(); showPreviewReady();
        // Mode atur arah: panorama baru mengganti sudut → terapkan ulang arah tersimpan.
        if (preview.dataset.mode === 'aim') { aimSeeked = false; aimEnableInteraction(); }
    };
    const loadPreviewNode = node => {
        if (!previewTourUrl) { previewStatus.textContent = 'URL tur tidak ditemukan — menampilkan thumbnail.'; previewStatus.hidden = false; return; }
        previewStatus.textContent = 'Memuat 360°…'; previewStatus.hidden = false;
        if (previewFrameState === 'ready') { openNodeInPreview(node); return; }
        if (previewFrameState === 'loading') return; // node terbaru dipakai saat player siap
        previewFrameState = 'loading';
        const started = Date.now();
        previewFrame.onload = () => {
            const wait = () => {
                const player = previewPlayer();
                if (!player) {
                    if (Date.now() - started > 20000) { previewFrameState = 'idle'; previewStatus.textContent = 'Preview 360° gagal dimuat — menampilkan thumbnail.'; return; }
                    setTimeout(wait, 150); return;
                }
                try {
                    const doc = previewFrame.contentDocument;
                    const observer = new MutationObserver(quietPreviewFrame);
                    observer.observe(doc.documentElement, {childList:true});
                    if (doc.body) observer.observe(doc.body, {childList:true});
                } catch (_) {}
                previewFrameState = 'ready';
                if (previewCard) openNodeInPreview(previewCard.dataset.panoramaNodeId);
            };
            wait();
        };
        previewFrame.src = previewTourUrl + (previewTourUrl.includes('?') ? '&' : '?') + 'cms_target_view=1#' + encodeURIComponent(node);
    };
    const renderPreview = card => {
        previewCard = card;
        const data = readPanorama(card), list = previewList(), index = list.indexOf(card);
        const title = data.title.trim() || 'Panorama ' + data.nodeId;
        document.getElementById('panoPreviewTitle').textContent = title;
        document.getElementById('panoPreviewMeta').textContent = data.nodeId + (data.customNodeId.trim() ? ' · ' + data.customNodeId.trim() : '') + (data.latitude.trim() && data.longitude.trim() ? ' · ' + data.latitude.trim() + ', ' + data.longitude.trim() : '');
        document.getElementById('panoPreviewCount').textContent = (index + 1) + ' / ' + list.length;
        const thumb = card.querySelector('.panorama-audit-thumb img');
        if (thumb) { previewImage.src = thumb.currentSrc || thumb.src; previewImage.hidden = false; } else { previewImage.removeAttribute('src'); previewImage.hidden = true; }
        document.getElementById('panoPreviewOpen').href = previewTourUrl ? previewTourUrl + '#' + encodeURIComponent(data.nodeId) : '#';
        previewTitleInput.value = data.title;
        const hotspotButton = card.querySelector('[data-see-panorama-hotspots]'), hotspotTarget = document.getElementById('panoPreviewHotspots');
        hotspotTarget.textContent = hotspotButton.disabled ? 'Belum ada hotspot' : hotspotButton.textContent.replace(/^\s*Lihat\s*/i, 'Lihat ');
        hotspotTarget.disabled = hotspotButton.disabled;
        preview.querySelectorAll('[data-preview-step]').forEach(button => { button.hidden = list.length < 2; });
        if (previewFrameState !== 'ready') previewFrame.classList.remove('ready');
        loadPreviewNode(data.nodeId);
    };
    const openPreview = card => {
        if (!card) return;
        preview.hidden = false; document.body.classList.add('modal-open');
        renderPreview(card);
        preview.querySelector('.pe-picker-x').focus({preventScroll:true});
    };
    const closePreview = () => {
        if (preview.hidden) return;
        if (preview.dataset.mode === 'aim') stopAimMode();
        preview.hidden = true; document.body.classList.remove('modal-open');
        try { previewPlayer()?.mute?.('_all'); } catch (_) {}
        const card = previewCard; previewCard = null;
        card?.querySelector('[data-preview-node]')?.focus({preventScroll:true});
    };
    const stepPreview = delta => {
        const list = previewList(); if (list.length < 2 || !previewCard) return;
        const index = list.indexOf(previewCard);
        renderPreview(list[(index + delta + list.length) % list.length]);
    };
    panoramaEditor.addEventListener('click', event => {
        const opener = event.target.closest('[data-preview-node]'); if (!opener) return;
        openPreview(opener.closest('[data-panorama-node-id]'));
    });
    hotspotEditor.addEventListener('click', event => {
        const thumb = event.target.closest('.pano-group-thumb[data-preview-node]'); if (!thumb) return;
        event.preventDefault(); // jangan buka/tutup accordion
        openPreview(document.querySelector('[data-panorama-node-id="' + CSS.escape(thumb.dataset.previewNode) + '"]'));
    });
    preview.addEventListener('click', event => {
        if (event.target.closest('[data-preview-close]')) { closePreview(); return; }
        const step = event.target.closest('[data-preview-step]'); if (step) stepPreview(Number(step.dataset.previewStep));
    });
    previewTitleInput.addEventListener('input', () => {
        if (!previewCard) return;
        const input = previewCard.querySelector('[data-panorama-field="title"]');
        input.value = previewTitleInput.value; input.dispatchEvent(new Event('input', {bubbles:true}));
        document.getElementById('panoPreviewTitle').textContent = previewTitleInput.value.trim() || 'Panorama ' + previewCard.dataset.panoramaNodeId;
    });
    previewTitleInput.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); stepPreview(1); previewTitleInput.focus(); previewTitleInput.select(); } });
    document.getElementById('panoPreviewHotspots').addEventListener('click', () => {
        const card = previewCard; closePreview();
        card?.querySelector('[data-see-panorama-hotspots]')?.click();
    });
    document.addEventListener('keydown', event => {
        if (preview.hidden) return;
        if (event.key === 'Escape') { closePreview(); return; }
        if (event.target === previewTitleInput) return;
        if (preview.dataset.mode === 'aim') return; // arah: jangan pindah panorama
        if (event.key === 'ArrowLeft') { event.preventDefault(); stepPreview(-1); }
        if (event.key === 'ArrowRight') { event.preventDefault(); stepPreview(1); }
    });

    // --- Atur arah hotspot (pan/tilt/fov) memakai pemutar 360° yang sama
    let aimInput = null, aimFrame = 0, aimSeeked = false;
    const aimBar = document.getElementById('panoAimBar');
    const aimLive = document.getElementById('panoAimLive');
    const aimValues = () => { const player = previewPlayer(); return {pan:Number(player?.getPan?.()||0), tilt:Number(player?.getTilt?.()||0), fov:Number(player?.getFov?.()||70)}; };
    const aimFormat = value => value.pan.toFixed(2) + '/' + value.tilt.toFixed(2) + '/' + value.fov.toFixed(2);
    function stopAimMode() {
        cancelAnimationFrame(aimFrame); aimFrame = 0; aimInput = null; aimSeeked = false;
        delete preview.dataset.mode;
        if (aimBar) aimBar.hidden = true;
        try { previewFrame.contentDocument?.getElementById('cms-aim-style')?.remove(); } catch (_) {}
    }
    const aimRefresh = () => {
        if (preview.dataset.mode !== 'aim') return;
        const player = previewPlayer();
        if (player && !aimSeeked) {
            // Terapkan arah tersimpan supaya pengguna mulai dari sudut lama.
            aimSeeked = true;
            const parts = String(aimInput?.value || '').split('/').map(Number);
            if (parts.length === 3 && parts.every(Number.isFinite)) {
                try { player.setPan?.(parts[0]); player.setTilt?.(parts[1]); player.setFov?.(parts[2]); } catch (_) {}
            }
            aimEnableInteraction();
        }
        const value = aimValues();
        if (aimLive) aimLive.textContent = 'Pan ' + value.pan.toFixed(1) + '° · Tilt ' + value.tilt.toFixed(1) + '° · FoV ' + value.fov.toFixed(1) + '°';
        aimFrame = requestAnimationFrame(aimRefresh);
    };
    const aimEnableInteraction = () => {
        try {
            const player = previewPlayer(), control = player?.control || player?.divSkin || null;
            // quietPreviewFrame() menyembunyikan divSkin; di mode arah div itu yang menerima geseran mouse.
            if (player?.divSkin?.style) { player.divSkin.style.display = ''; player.divSkin.style.pointerEvents = 'auto'; }
            if (control && !control.id) control.id = 'cms-aim-control';
            const controlSel = control ? '#' + control.id : '#hotspots';
            const frameDocument = previewFrame.contentDocument;
            if (frameDocument && !frameDocument.getElementById('cms-aim-style')) {
                // Semua lapisan HUD (di dalam maupun di luar #container, termasuk yang ditempel ke body)
                // disembunyikan lewat CSS; canvas + lapisan kontrol tetap terlihat & bisa digeser.
                const style = frameDocument.createElement('style'); style.id = 'cms-aim-style';
                style.textContent = 'html>*:not(body){display:none!important}'
                    + 'body>*:not(#container){display:none!important}'
                    + '#container *{visibility:hidden!important}'
                    + '#container canvas,#container canvas *{visibility:visible!important}'
                    + controlSel + '{visibility:visible!important;display:block!important;cursor:crosshair}'
                    + controlSel + '>*{visibility:hidden!important}';
                (frameDocument.head || frameDocument.documentElement).appendChild(style);
            }
            try { player?.stopAutorotate?.(); } catch (_) {}
        } catch (_) {}
    };
    const startAim = card => {
        const input = card.querySelector('[data-pano-field="target"]'); if (!input) return;
        if (card.querySelector('[data-hs-pane="pano"]')?.hidden) { alert('Ubah jenis tujuan ke Panorama dulu untuk mengatur arah pandang.'); return; }
        const target = card.querySelector('[data-hs-target-select]')?.value?.trim() || '';
        if (!target) { alert('Pilih panorama tujuan dulu sebelum mengatur arah pandang.'); return; }
        const targetCard = document.querySelector('[data-panorama-node-id="' + CSS.escape(target) + '"]');
        if (!targetCard) { alert('Panorama tujuan tidak ditemukan di daftar panorama.'); return; }
        aimInput = input; aimSeeked = false;
        preview.dataset.mode = 'aim'; // set sebelum openPreview agar iframe tidak ditutup skin-mode
        preview.querySelectorAll('[data-preview-step]').forEach(button => { button.hidden = true; });
        aimBar.hidden = false;
        openPreview(targetCard);
        cancelAnimationFrame(aimFrame); aimRefresh();
    };
    hotspotEditor.addEventListener('click', event => {
        const button = event.target.closest('[data-hs-aim]'); if (!button) return;
        startAim(button.closest('.pano-edit-card'));
    });
    // Tombol "Simpan arah" merah muda (menggantikan yang hijau, hanya untuk template yang punya setup.json): simpan arah + pano.xml, lalu jalankan aksi setup template
    // (mis. perbarui versi pano.xml di index.html agar browser memuat view baru).
    const runTourSetup = async verify => {
        const response = await fetch('api.php?action=tour-setup', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>, id_lokasi:<?= json_encode($auditId) ?>, verify})});
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Setup gagal.');
        return result.message;
    };
    document.getElementById('panoAimSetup')?.addEventListener('click', async event => {
        const button = event.currentTarget; if (!aimInput || button.disabled) return;
        const aimCard = aimInput.closest('.pano-edit-card'), want = aimFormat(aimValues());
        const verify = {nodeId:aimCard?.dataset.nodeId, id:aimCard?.dataset.hotspotId, target:want};
        aimInput.value = want;
        aimInput.dispatchEvent(new Event('input', {bubbles:true}));
        closePreview();
        button.disabled = true;
        try {
            refreshDirty();   // event input baru dihitung di frame berikutnya; hitung sekarang agar savePano melihat arah baru
            await savePano();   // menyimpan semua perubahan pano.xml (termasuk arah ini)
            if (dirtyCount > 0 || saving) throw new Error('arah belum tersimpan ke pano.xml (lihat pesan di atas). Setup dibatalkan.');   // gagal/validasi: pesan sudah ditampilkan savePano, jangan lanjut Setup
            flash('Menjalankan setup…', 'busy');
            const message = await runTourSetup(verify);
            flash('✓ Arah disimpan. ' + message, 'success'); hideTimer = setTimeout(() => { if (!dirtyCount && !saving) saveBar.classList.remove('visible'); }, 6000);
        } catch (error) { flash('Gagal: ' + error.message, 'error'); }
        finally { button.disabled = false; }
    });
    document.getElementById('panoAimSave')?.addEventListener('click', () => {
        if (!aimInput) return;
        aimInput.value = aimFormat(aimValues());
        aimInput.dispatchEvent(new Event('input', {bubbles:true}));
        closePreview();
    });
    document.getElementById('panoAimCancel')?.addEventListener('click', () => closePreview());

    // --- Terjemahan
    let activeTranslationFilter = 'all';
    const refreshTranslationRow = row => {
        const emptyFields = Array.from(row.querySelectorAll('textarea[data-translation-index]:not([hidden])')).filter(field => !field.value.trim());
        row.classList.toggle('needs-translation', emptyFields.length > 0);
        row.querySelectorAll('.translation-fields label').forEach(label => label.classList.toggle('empty-translation', !label.querySelector('textarea').value.trim()));
        let warning = row.querySelector('.translation-warning');
        if (emptyFields.length > 0 && !warning) { warning=document.createElement('span');warning.className='translation-warning';row.querySelector('.translation-source').after(warning); }
        if (warning) { warning.textContent='! '+emptyFields.length+' bahasa kosong';warning.hidden=emptyFields.length===0; }
        row.hidden=activeTranslationFilter==='missing'&&emptyFields.length===0;
        syncTranslationCounters();
    };
    document.querySelectorAll('[data-translation-filter]').forEach(button=>button.addEventListener('click',()=>{
        document.querySelectorAll('[data-translation-filter]').forEach(item=>item.classList.remove('active'));button.classList.add('active');activeTranslationFilter=button.dataset.translationFilter;
        translationRows().forEach(refreshTranslationRow);
    }));
    document.getElementById('panoTranslationEditor')?.addEventListener('input',event=>{if(event.target.matches('[data-translation-index]'))refreshTranslationRow(event.target.closest('.translation-row'));});
    // Terjemahan otomatis: tombol ✦ per baris dan tombol besar "Terjemahkan Semua" (dengan indikator progres).
    const translateLanguages = <?= json_encode($auditResult['languages'] ?? []) ?>;
    const translateTargets = <?= json_encode(array_reduce(array_keys($languageRegistry), static function(array $map, string $code) use ($languageRegistry): array { $map[$code] = $languageRegistry[$code]['translate']; $map[$languageRegistry[$code]['pano']] = $languageRegistry[$code]['translate']; return $map; }, []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const translateRowFields = async row => {
        const source = row.dataset.source; let filled = 0;
        for (const field of row.querySelectorAll('[data-translation-index]')) {
            if (field.value.trim()) continue;
            const language = translateLanguages[Number(field.dataset.translationIndex)], target = translateTargets[language] || language;
            const response = await fetch('https://translate.googleapis.com/translate_a/single?client=gtx&sl=id&tl=' + encodeURIComponent(target) + '&dt=t&q=' + encodeURIComponent(source));
            if (!response.ok) throw new Error('Terjemahan ' + (language || '') + ' gagal.');
            const data = await response.json(); field.value = (data[0] || []).map(part => part[0] || '').join(''); filled++;
        }
        refreshTranslationRow(row); return filled;
    };
    document.querySelectorAll('[data-translate-pano]').forEach(function(button) {
        button.addEventListener('click', async function() {
            const row = button.closest('.translation-row');
            button.disabled = true; button.classList.add('is-loading'); row.classList.add('is-translating');
            try { await translateRowFields(row); }
            catch (error) { alert('Auto translate gagal: ' + error.message); }
            finally { button.disabled = false; button.classList.remove('is-loading'); row.classList.remove('is-translating'); refreshTranslationRow(row); refreshDirty(); refreshTranslateAll(); }
        });
    });
    const translateAllButton = document.getElementById('translateAllPano');
    const translateAllLabel = translateAllButton?.querySelector('.ta-label');
    let translateAllRunning = false, translateAllReset = 0;
    const pendingTranslationRows = () => translationRows().filter(row => Array.from(row.querySelectorAll('[data-translation-index]')).some(field => !field.value.trim()));
    function setTranslateAllProgress(done, total) {
        translateAllButton.style.setProperty('--p', total ? (done / total * 100).toFixed(1) + '%' : '0%');
        translateAllLabel.textContent = 'Menerjemahkan… ' + done + '/' + total;
    }
    function refreshTranslateAll() {
        if (!translateAllButton || translateAllRunning) return;
        clearTimeout(translateAllReset);
        const pending = pendingTranslationRows().length;
        translateAllButton.style.setProperty('--p', '0%');
        translateAllButton.dataset.state = pending ? 'idle' : 'complete';
        translateAllButton.disabled = pending === 0;
        translateAllLabel.textContent = pending ? 'Terjemahkan Semua (' + pending + ')' : '\u2713 Semua sudah diterjemahkan';
        const missingFilter = document.querySelector('[data-translation-filter="missing"]');
        if (missingFilter) missingFilter.textContent = 'Belum Diisi (' + translationRows().filter(row => row.classList.contains('needs-translation')).length + ')';
    }
    translateAllButton?.addEventListener('click', async () => {
        if (translateAllRunning) return;
        const queue = pendingTranslationRows(), total = queue.length; if (!total) return;
        translateAllRunning = true; translateAllButton.disabled = true; translateAllButton.dataset.state = 'running';
        let finished = 0, failed = 0; setTranslateAllProgress(0, total);
        const worker = async () => {
            while (queue.length) {
                const row = queue.shift(), rowButton = row.querySelector('[data-translate-pano]');
                rowButton?.classList.add('is-loading'); row.classList.add('is-translating');
                try { await translateRowFields(row); } catch (_) { failed++; }
                rowButton?.classList.remove('is-loading'); row.classList.remove('is-translating');
                finished++; setTranslateAllProgress(finished, total); refreshDirty();
            }
        };
        await Promise.all([worker(), worker(), worker()]);
        translateAllRunning = false; translateAllButton.disabled = false;
        translateAllButton.style.setProperty('--p', '100%');
        translateAllButton.dataset.state = failed ? 'partial' : 'finished';
        translateAllLabel.textContent = failed ? 'Selesai, ' + failed + ' gagal \u2014 klik untuk ulangi' : '\u2713 Selesai \u00b7 ' + total + ' teks diterjemahkan';
        flash(failed ? failed + ' teks gagal diterjemahkan. Klik tombol lagi untuk mengulang yang kosong.' : '\u2713 Terjemahan selesai. Tekan Simpan pano.xml untuk menyimpan.', failed ? 'error' : 'success');
        if (!failed) celebrateStars(translateAllButton);
        translateAllReset = setTimeout(refreshTranslateAll, 5000);
    });
    document.getElementById('panoTranslationEditor')?.addEventListener('input', () => refreshTranslateAll());

    // ---- Angka "Terjemahan kosong" ikut berubah langsung; saat mencapai 0 angkanya "terbakar" (api), dan tombol memercikkan bintang.
    const warningCard = document.querySelector('.audit-summary .audit-warning');
    const warningValue = warningCard?.querySelector('strong');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let shownWarnings = Number(warningValue?.textContent) || 0, warningFrame = 0;
    function countEmptyTranslations() {
        return translationRows().reduce((sum, row) => sum + Array.from(row.querySelectorAll('textarea[data-translation-index]:not([hidden])')).filter(field => !field.value.trim()).length, 0);
    }
    function setWarningBurning(on) {
        if (!warningCard) return;
        warningCard.classList.toggle('is-zero', on);
        let flame = warningCard.querySelector('.fire-icon');
        if (on && !flame) { flame = document.createElement('span'); flame.className = 'fire-icon'; flame.setAttribute('aria-hidden', 'true'); flame.textContent = '\uD83D\uDD25'; warningCard.append(flame); }
        if (!on) flame?.remove();
    }
    function igniteWarningCard() {
        setWarningBurning(true);
        if (reduceMotion || !warningCard) return;
        warningCard.classList.remove('ignite'); void warningCard.offsetWidth; warningCard.classList.add('ignite');
        const rect = warningCard.getBoundingClientRect();
        for (let i = 0; i < 14; i++) {
            const flame = document.createElement('span');
            flame.className = 'fire-particle'; flame.textContent = '\uD83D\uDD25';
            flame.style.left = (rect.left + 28 + Math.random() * 70) + 'px'; flame.style.top = (rect.top + 26 + Math.random() * 20) + 'px';
            flame.style.setProperty('--dx', ((Math.random() - .5) * 90).toFixed(0) + 'px'); flame.style.setProperty('--dy', (-(50 + Math.random() * 90)).toFixed(0) + 'px');
            flame.style.setProperty('--s', (.7 + Math.random() * .9).toFixed(2)); flame.style.animationDelay = (Math.random() * .35).toFixed(2) + 's';
            document.body.append(flame); flame.addEventListener('animationend', () => flame.remove(), {once:true});
        }
    }
    function syncTranslationCounters(immediate = false) {
        const total = countEmptyTranslations();
        const missingFilter = document.querySelector('[data-translation-filter="missing"]');
        if (missingFilter) missingFilter.textContent = 'Belum Diisi (' + translationRows().filter(row => row.classList.contains('needs-translation')).length + ')';
        if (!warningValue || total === shownWarnings) return;
        cancelAnimationFrame(warningFrame);
        const from = shownWarnings;
        if (immediate || reduceMotion) { warningValue.textContent = shownWarnings = total; setWarningBurning(total === 0); return; }
        if (total > 0) setWarningBurning(false);
        const start = performance.now(), duration = Math.min(1100, 300 + Math.abs(from - total) * 16);
        const step = now => {
            const t = Math.min(1, (now - start) / duration), eased = 1 - Math.pow(1 - t, 3);
            shownWarnings = Math.round(from + (total - from) * eased); warningValue.textContent = shownWarnings;
            if (t < 1) warningFrame = requestAnimationFrame(step);
            else { shownWarnings = total; warningValue.textContent = total; if (total === 0) igniteWarningCard(); }
        };
        warningFrame = requestAnimationFrame(step);
    }
    function celebrateStars(button) {
        if (reduceMotion || !button) return;
        const rect = button.getBoundingClientRect(), glyphs = ['\u2726', '\u2727', '\u2605', '\u2736'], colors = ['#fde047', '#ffffff', '#c4b5fd', '#f9a8d4', '#86efac'];
        for (let i = 0; i < 22; i++) {
            const star = document.createElement('span'), angle = Math.random() * Math.PI * 2, distance = 50 + Math.random() * 90;
            star.className = 'sparkle-particle'; star.textContent = glyphs[i % glyphs.length]; star.style.color = colors[i % colors.length];
            star.style.left = (rect.left + rect.width / 2 + (Math.random() - .5) * rect.width * .8) + 'px'; star.style.top = (rect.top + rect.height / 2 + (Math.random() - .5) * rect.height * .6) + 'px';
            star.style.setProperty('--dx', (Math.cos(angle) * distance).toFixed(0) + 'px'); star.style.setProperty('--dy', (Math.sin(angle) * distance * .8).toFixed(0) + 'px');
            star.style.setProperty('--s', (.7 + Math.random() * 1.1).toFixed(2)); star.style.setProperty('--r', ((Math.random() - .5) * 240).toFixed(0) + 'deg'); star.style.animationDelay = (Math.random() * .25).toFixed(2) + 's';
            document.body.append(star); star.addEventListener('animationend', () => star.remove(), {once:true});
        }
    }
    syncTranslationCounters(true);
    refreshTranslateAll();

    // --- Simpan
    const savePano = async () => {
        if (saving) return;
        const invalid = validateCustomIds();
        if (invalid) {
            flash(invalid + ' Custom Node ID belum valid. Perbaiki dulu sebelum menyimpan.', 'error');
            showEditorTab('panorama'); panoramaSearch.value = ''; panoramaFilter = 'all';
            document.querySelectorAll('[data-panorama-filter]').forEach(item => item.classList.toggle('active', item.dataset.panoramaFilter === 'all')); applyPanoramaView();
            const first = document.querySelector('.panorama-audit-card.has-error'); first?.scrollIntoView({behavior:'smooth', block:'center'}); first?.querySelector('[data-panorama-field="customNodeId"]').focus({preventScroll:true});
            return;
        }
        if (dirtyCount === 0) { flash('Tidak ada perubahan untuk disimpan.'); hideTimer = setTimeout(() => saveBar.classList.remove('visible'), 2000); return; }
        saving = true; panoSaveButton.disabled = saveBottom.disabled = true; flash('Menyimpan pano.xml…', 'busy');
        const hotspots = hotspotCards().map(readHotspot), panoramas = panoramaCards().map(readPanorama);
        const translations = {}; translationRows().forEach(row => { translations[row.dataset.source] = readTranslation(row); });
        try {
            const response = await fetch('save-pano.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({csrf:<?= json_encode($_SESSION['cms_csrf']) ?>, id_lokasi:<?= json_encode($auditId) ?>, hotspots, translations, panoramas})});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Gagal menyimpan.');
            removeDeletedHotspots(); takeBaseline(); saving = false; refreshDirty();
            flash('✓ ' + result.message, 'success');
            hideTimer = setTimeout(() => { if (!dirtyCount && !saving) saveBar.classList.remove('visible'); }, 3500);
        } catch (error) {
            saving = false; panoSaveButton.disabled = saveBottom.disabled = dirtyCount === 0;
            flash('Gagal: ' + error.message, 'error');
        }
    };
    panoSaveButton.addEventListener('click', savePano);
    saveBottom.addEventListener('click', savePano);
    document.getElementById('panoDiscard').addEventListener('click', () => {
        if (!dirtyCount || !confirm('Batalkan ' + dirtyCount + ' perubahan yang belum disimpan?')) return;
        allowEditorNavigation = true; window.cmsPanoEditorDirty = false; location.reload();
    });
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && !event.altKey && event.key.toLowerCase() === 's') { event.preventDefault(); savePano(); }
    });
    window.addEventListener('beforeunload', event => { if (window.cmsPanoEditorDirty && !allowEditorNavigation) { event.preventDefault(); event.returnValue = ''; } });

    // --- Mulai
    hotspotCards().forEach(refreshHotspotState); refreshGroupCounts(); validateCustomIds();
    takeBaseline(); refreshDirty();
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
                    const target = document.querySelector('[name="' + field + '[' + fieldCode + ']"]');
                    target.value = await translateText(text, apiCode);
                    // nilai yang diisi lewat kode tidak memicu event: beri tahu penjaga perubahan agar tombol Simpan aktif
                    target.dispatchEvent(new Event('input', { bubbles: true }));
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
                    // Mode atur arah: hanya panorama yang tampil. Semua lapisan di atasnya (skin Jakarta, Smart Kit, onboarding,
                    // pin) disembunyikan lewat CSS di dalam iframe. PENTING: elemen kontrol mouse Pano2VR (player.control) adalah
                    // div skin/hotspot itu sendiri, jadi div-nya harus tetap tampil dan menerima mouse; yang disembunyikan hanya isinya.
                    const control=player.control||player.divSkin||null;
                    if(control&&!control.id)control.id='cms-pano-control';
                    const controlSel=control?'#'+(control.id||'hotspots'):'#hotspots';
                    if(frameDocument&&!frameDocument.getElementById('cms-target-view-style')){
                        const style=frameDocument.createElement('style');style.id='cms-target-view-style';
                        style.textContent='html>:not(head):not(body),body>:not(#container):not(script):not(link):not(style):not(noscript){display:none!important}'
                            +'#container #smt-pinlayer,#container #hotspottext{display:none!important}'
                            +controlSel+'>*{display:none!important}'
                            +controlSel+'{display:block!important;visibility:visible!important;pointer-events:auto!important;cursor:grab}'
                            +controlSel+':active{cursor:grabbing}';
                        (frameDocument.head||frameDocument.documentElement).appendChild(style);
                    }
                    const removeFrameOverlays=()=>{frameDocument?.getElementById('jakarta-skin-overlay')?.remove();frameDocument?.getElementById('jakarta-skin-boot-cover')?.remove();};
                    removeFrameOverlays();
                    if(frameDocument?.documentElement)new MutationObserver(removeFrameOverlays).observe(frameDocument.documentElement,{childList:true,subtree:true});
                    try{player.stopAutorotate?.();}catch(_){}
                    try{player.setLockedMouse?.(false);player.setLockedWheel?.(false);}catch(_){}
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
document.getElementById('saveDroneView')?.addEventListener('click',()=>{const value=droneViewValues();document.getElementById('droneInput').value=pendingDroneNode;document.getElementById('dronePan').value=value.pan.toFixed(2);document.getElementById('droneTilt').value=value.tilt.toFixed(2);document.getElementById('droneFov').value=value.fov.toFixed(2);document.getElementById('droneViewSummary').textContent='Target view: pan '+value.pan.toFixed(1)+'° · tilt '+value.tilt.toFixed(1)+'° · FoV '+value.fov.toFixed(1)+'°';document.getElementById('droneInput').dispatchEvent(new Event('input',{bubbles:true}));closePanoramaPicker();});
document.getElementById('openPanoramaPicker')?.addEventListener('click', openPanoramaPicker);
document.querySelectorAll('[data-close-panorama]').forEach(function (element) { element.addEventListener('click', closePanoramaPicker); });
document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closePanoramaPicker(); });

const voiceDatabase = <?= json_encode($database, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const oiDatabase = <?= json_encode($oiDatabase, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const detectedWebElements = <?= json_encode($selectedWebElements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const detectedObjectHotspots = <?= json_encode($selectedHotspots, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const oiPreviewBaseUrl = <?= json_encode($oiPreviewBaseUrl, JSON_UNESCAPED_SLASHES) ?>;
const objectTourBaseUrl = <?= json_encode(isset($detectedTours[$selectedId]) ? projectUrl(trim($detectedTours[$selectedId]['path'], '/') . '/') : '', JSON_UNESCAPED_SLASHES) ?>;
const currentLocationId = <?= json_encode($selectedId) ?>;
// projectUrl() is relative to the CMS page (for example ../dj-output/).
// Resolve it once because the URL constructor requires an absolute base URL.
const activeProjectBaseUrl = new URL(<?= json_encode(projectUrl(), JSON_UNESCAPED_SLASHES) ?>, document.baseURI).href;
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
    let editableWebElements = structuredClone(detectedWebElements);
    let editableObjectHotspots = structuredClone(detectedObjectHotspots);
    let selectedObject = objectRecords.length ? 0 : -1;
    let selectedWebElement = -1;
    let objectPanoPlayer = null, objectPanoXml = null, objectPanoReady = false, objectPanoDragging = false, objectSwitching = false, objectSwitchTimer = 0;
    const objectForm = document.getElementById('objectForm');
    const objectSearch = document.getElementById('objectSearch');
    const escapeObjectHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
    // Route Key object baru = node + nama lokasi + target (mis. node115-pantai-legian-element02) agar unik di antara semua lokasi.
    const makeObjectRouteKey = (nodeId, targetId) => {
        const clean = value => String(value || '').toLowerCase().replace(/[^a-z0-9_-]+/g, '-').replace(/^-+|-+$/g, '');
        const base = [clean(nodeId), clean(currentLocationId), clean(targetId)].filter(Boolean).join('-');
        let key = base, number = 2;
        while (objectRecords.some(record => String(record.route_key || '').toLowerCase() === key)) key = base + '-' + number++;
        return key;
    };
    const blankObject = () => ({route_key:'objectbaru',infoID:'',nodeID:'',targetID:'Element01',category:'umum',arah:'luruskanan',versi:'1',ukuran:'500px',labelTeks:Object.fromEntries(Object.keys(voiceLabels).map(code=>[code,''])),isActive:true,title:''});
    // Nama di daftar = teks setelah <br>/</br> pada label Indonesia (mis. "Klik untuk Informasi </br>Patung Masolah" → "Patung Masolah").
    const objectDisplayName = record => {
        const text = String(record.labelTeks?.id || '').trim();
        const parts = text.split(/<\s*\/?\s*br\s*\/?\s*>/i);
        return (parts.length > 1 ? parts[parts.length - 1].trim() : '') || text || String(record.title || '').trim() || 'Tanpa judul';
    };
    // Daftar: object aktif di atas, nonaktif di bawah (urutan asli dipertahankan di tiap kelompok). Hanya tampilan; urutan data tidak berubah.
    function renderObjectItems(animate = false) {
        // FLIP: catat posisi lama tiap item, render ulang, lalu animasikan dari posisi lama ke posisi baru.
        const before = new Map();
        if (animate) objectList.querySelectorAll('[data-object]').forEach(item => before.set(item.dataset.object, item.getBoundingClientRect().top));
        const query = objectSearch.value.trim().toLowerCase();
        const visible = objectRecords.map((record,index)=>({record,index})).filter(({record}) => !query || [record.route_key,record.nodeID,record.labelTeks?.id,objectDisplayName(record)].some(value=>String(value||'').toLowerCase().includes(query)));
        visible.sort((a,b) => (b.record.isActive ? 1 : 0) - (a.record.isActive ? 1 : 0) || a.index - b.index);
        const activeTotal = objectRecords.filter(record => record.isActive).length, offTotal = objectRecords.length - activeTotal;
        const counter = document.getElementById('objectCount');
        if (counter) counter.innerHTML = '<span class="oi-chip oi-chip-on' + (animate ? ' pop' : '') + '"><i></i><b>' + activeTotal + '</b> Aktif</span><span class="oi-chip oi-chip-off' + (animate ? ' pop' : '') + '"><i></i><b>' + offTotal + '</b> Nonaktif</span>' + (visible.length !== objectRecords.length ? '<span class="oi-chip">' + visible.length + ' dari ' + objectRecords.length + '</span>' : '');
        objectList.innerHTML = visible.map(({record,index}) => '<button type="button" class="object-item '+(record.isActive?'is-on':'is-off')+(index===selectedObject?' active':'')+'" data-object="'+index+'"><span class="oi-row"><strong>'+escapeObjectHtml(objectDisplayName(record))+'</strong><em class="oi-state">'+(record.isActive?'Aktif':'Nonaktif')+'</em></span><small>'+escapeObjectHtml(record.route_key)+' · '+escapeObjectHtml(record.nodeID||'Tanpa node')+'</small></button>').join('');
        if (animate && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            objectList.querySelectorAll('[data-object]').forEach(item => {
                const from = before.get(item.dataset.object); if (from === undefined) return;
                const delta = from - item.getBoundingClientRect().top; if (Math.abs(delta) < 1) return;
                item.style.zIndex = '1';
                item.animate([{transform:'translateY(' + delta + 'px)'},{transform:'translateY(0)'}], {duration:420, easing:'cubic-bezier(.22,.9,.28,1)'}).onfinish = () => { item.style.zIndex = ''; };
            });
        }
    }
    function renderObjectList() {
        renderObjectItems();
        renderObjectForm();
        syncDeleteButton();
        // Belum ada object sama sekali: panel daftar & form disembunyikan (muncul sendiri begitu object pertama ditambahkan).
        document.getElementById('oiStageWrap')?.classList.toggle('oi-empty', objectRecords.length === 0);
    }
    const recordMatchesElement = (record, element) => String(record.nodeID || '').replace(/[{}]/g, '').toLowerCase() === String(element.nodeID).toLowerCase() && String(record.targetID || '').toLowerCase() === String(element.targetID).toLowerCase();
    function syncDeleteButton() {
        const button = document.getElementById('removeWebElement');
        if (button) button.disabled = !(objectRecords[selectedObject] || editableWebElements[selectedWebElement]);
    }
    // Ikon & warna pin: Objek Info (biru), Hotspot darat (hijau), Drone (ungu).
    const objectPinIcons = {
        info: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><circle cx="12" cy="7.8" r=".6" fill="currentColor"/></svg>',
        land: '<svg viewBox="0 0 24 24"><path d="M12 3l7 17-7-4-7 4z" fill="currentColor" fill-opacity=".28"/></svg>',
        drone: '<svg viewBox="0 0 24 24"><path d="M8 8l8 8M16 8l-8 8"/><circle cx="6" cy="6" r="2.6"/><circle cx="18" cy="6" r="2.6"/><circle cx="6" cy="18" r="2.6"/><circle cx="18" cy="18" r="2.6"/><rect x="9.5" y="9.5" width="5" height="5" rx="1.4"/></svg>'
    };
    const objectHotspotKind = hotspot => (/drone/i.test(String(hotspot.skin || '')) || /^\s*drone\b/i.test(String(hotspot.title || ''))) ? 'drone' : 'land';
    const normalizeObjectPan = value => ((Number(value) + 180) % 360 + 360) % 360 - 180;
    const panoramaSelect = document.getElementById('objectPanoramaNode');
    const panoramaHost = document.getElementById('objectPanoramaHost');
    const panoramaStatus = document.getElementById('objectPanoramaStatus');
    const setPanoramaStatus = message => { if (panoramaStatus) panoramaStatus.textContent = message; };
    const currentPublicNode = () => panoramaSelect?.selectedOptions[0]?.dataset.publicNode || '';
    function selectWebElement(index, refreshMarkers = true) {
        selectedWebElement = index;
        const element = editableWebElements[index];
        syncDeleteButton();
        if (element) {
            const recordIndex = objectRecords.findIndex(record => String(record.nodeID || '').replace(/[{}]/g, '').toLowerCase() === String(element.nodeID).toLowerCase() && String(record.targetID || '').toLowerCase() === String(element.targetID).toLowerCase());
            if (recordIndex >= 0) { selectedObject = recordIndex; objectSearch.value = ''; renderObjectList(); }
        }
        if (refreshMarkers) renderWebElementMarkers();
        else panoramaHost?.querySelectorAll('.object-web-pin').forEach(marker => marker.classList.toggle('selected', marker.title === element?.targetID));
    }
    function attachWebElementDrag(button, element, index) {
        button.addEventListener('pointerdown', event => {
            if (event.button !== 0 || !objectPanoReady || objectPanoDragging) return;
            event.preventDefault(); event.stopPropagation(); selectWebElement(index, false);
            const rect = panoramaHost.getBoundingClientRect();
            const first = objectPanoPlayer.getPositionAngles(event.clientX - rect.left, event.clientY - rect.top);
            const startPan = Number(element.pan);
            const tiltOffset = Number(element.tilt) - Number(first.tilt);
            const historyBefore = historySnapshot(); objectPanoDragging = true; objectPanoPlayer.stopAutorotate?.(); objectPanoPlayer.setLocked(true); button.setPointerCapture?.(event.pointerId);
            const move = moveEvent => {
                moveEvent.preventDefault(); moveEvent.stopPropagation();
                const area = panoramaHost.getBoundingClientRect(), angle = objectPanoPlayer.getPositionAngles(moveEvent.clientX - area.left, moveEvent.clientY - area.top);
                const panDelta = normalizeObjectPan(Number(angle.pan) - Number(first.pan));
                element.pan = normalizeObjectPan(startPan - panDelta); element.tilt = Math.max(-90, Math.min(90, Number(angle.tilt) + tiltOffset));
                objectPanoPlayer.updateHotspot('cms_we_' + index, element.pan, element.tilt);
                setPanoramaStatus(element.targetID + ' · Pan ' + element.pan.toFixed(2) + '° · Tilt ' + element.tilt.toFixed(2) + '°');
            };
            const end = endEvent => {
                endEvent?.preventDefault(); endEvent?.stopPropagation(); objectPanoDragging = false; objectPanoPlayer.setLocked(false); commitHistory(historyBefore, 'Geser ' + element.targetID);
                button.removeEventListener('pointermove', move); button.removeEventListener('pointerup', end); button.removeEventListener('pointercancel', end);
                setPanoramaStatus('Posisi ' + element.targetID + ' diperbarui. Tekan Simpan untuk menerapkan ke pano.xml.');
            };
            button.addEventListener('pointermove', move); button.addEventListener('pointerup', end); button.addEventListener('pointercancel', end);
        });
    }
    function attachObjectHotspotDrag(button, hotspot, index) {
        button.addEventListener('pointerdown',event=>{if(event.button!==0||!objectPanoReady||objectPanoDragging)return;event.preventDefault();event.stopPropagation();const rect=panoramaHost.getBoundingClientRect(),first=objectPanoPlayer.getPositionAngles(event.clientX-rect.left,event.clientY-rect.top),startPan=Number(hotspot.pan),tiltOffset=Number(hotspot.tilt)-Number(first.tilt);const historyBefore=historySnapshot();objectPanoDragging=true;objectPanoPlayer.stopAutorotate?.();objectPanoPlayer.setLocked(true);button.setPointerCapture?.(event.pointerId);const move=moveEvent=>{moveEvent.preventDefault();moveEvent.stopPropagation();const area=panoramaHost.getBoundingClientRect(),angle=objectPanoPlayer.getPositionAngles(moveEvent.clientX-area.left,moveEvent.clientY-area.top),panDelta=normalizeObjectPan(Number(angle.pan)-Number(first.pan));hotspot.pan=normalizeObjectPan(startPan-panDelta);hotspot.tilt=Math.max(-90,Math.min(90,Number(angle.tilt)+tiltOffset));objectPanoPlayer.updateHotspot('cms_hs_'+index,hotspot.pan,hotspot.tilt);setPanoramaStatus('Hotspot '+hotspot.id+' · Pan '+hotspot.pan.toFixed(2)+'° · Tilt '+hotspot.tilt.toFixed(2)+'°');};const end=endEvent=>{endEvent?.preventDefault();endEvent?.stopPropagation();objectPanoDragging=false;objectPanoPlayer.setLocked(false);commitHistory(historyBefore,'Geser hotspot '+hotspot.id);button.removeEventListener('pointermove',move);button.removeEventListener('pointerup',end);button.removeEventListener('pointercancel',end);setPanoramaStatus('Posisi hotspot '+hotspot.id+' diperbarui. Tekan Simpan untuk menerapkan.');};button.addEventListener('pointermove',move);button.addEventListener('pointerup',end);button.addEventListener('pointercancel',end);});
    }
    function renderWebElementMarkers() {
        if (!objectPanoPlayer || !objectPanoReady || objectPanoDragging || !panoramaSelect) return;
        objectPanoPlayer.removeHotspots();
        editableObjectHotspots.forEach((hotspot,index)=>{if(String(hotspot.panoNodeId).toLowerCase()!==panoramaSelect.value.toLowerCase())return;const marker=document.createElement('button');marker.type='button';const kind=objectHotspotKind(hotspot);marker.className='object-web-pin object-hotspot-pin pin-'+kind;marker.title=(kind==='drone'?'Drone ':'Hotspot ')+hotspot.id;marker.innerHTML='<i>'+objectPinIcons[kind]+'</i><span>'+escapeObjectHtml(hotspot.title||hotspot.id)+'</span>';attachObjectHotspotDrag(marker,hotspot,index);objectPanoPlayer.addHotspot('cms_hs_'+index,normalizeObjectPan(hotspot.pan),Number(hotspot.tilt),marker);});
        editableWebElements.forEach((element, index) => {
            if (String(element.panoNodeId).toLowerCase() !== panoramaSelect.value.toLowerCase()) return;
            const marker = document.createElement('button'); marker.type = 'button'; marker.className = 'object-web-pin pin-info' + (index === selectedWebElement ? ' selected' : ''); marker.title = 'Objek Info ' + element.targetID; marker.innerHTML = '<i>' + objectPinIcons.info + '</i><span>' + escapeObjectHtml(element.targetID) + '</span>';
            marker.addEventListener('click', event => { event.stopPropagation(); selectWebElement(index); }); attachWebElementDrag(marker, element, index);
            objectPanoPlayer.addHotspot('cms_we_' + index, normalizeObjectPan(element.pan), Number(element.tilt), marker);
        });
    }
    async function ensureObjectPanoPlayer() {
        if (!panoramaHost || !panoramaSelect || !objectTourBaseUrl) return;
        const base = new URL(objectTourBaseUrl, document.baseURI);
        if (!window.pano2vrPlayer) await new Promise((resolve, reject) => { const script = document.createElement('script'); script.src = new URL('pano2vr_player.js', base).href; script.onload = resolve; script.onerror = () => reject(new Error('pano2vr_player.js tidak dapat dimuat.')); document.head.appendChild(script); });
        if (!objectPanoXml) { const response = await fetch(new URL('pano.xml?v=' + Date.now(), base), {cache:'no-store'}); if (!response.ok) throw new Error('pano.xml tidak dapat dibaca.'); objectPanoXml = new DOMParser().parseFromString(await response.text(), 'application/xml'); if (objectPanoXml.querySelector('parsererror')) throw new Error('pano.xml tidak valid.'); }
        if (!objectPanoPlayer) { objectPanoPlayer = new pano2vrPlayer('objectPanoramaHost'); objectPanoPlayer.addListener('changenode', finishObjectSwitch); }
        const config = objectPanoXml.cloneNode(true); config.documentElement.setAttribute('start', panoramaSelect.value);
        config.querySelectorAll('hotspots,media,animation,autorotate').forEach(node => node.remove());
        config.querySelectorAll('input[leveltileurl],input[previewurl]').forEach(input => ['leveltileurl','previewurl'].forEach(attribute => { const value=input.getAttribute(attribute); if(value) input.setAttribute(attribute,new URL(value.replace(/^\.\//,''),base).href); }));
        config.querySelectorAll('control').forEach(control => { control.setAttribute('lockedmouse','0'); control.setAttribute('lockedwheel','0'); control.setAttribute('lockedkeyboard','0'); });
        objectPanoReady = false; objectPanoPlayer.readConfigString(new XMLSerializer().serializeToString(config));
        window.setTimeout(() => { objectPanoReady = true; document.getElementById('objectPanoramaLoading').hidden = true; renderWebElementMarkers(); setPanoramaStatus('Panorama siap. Tarik pin untuk memindahkan WebElement.'); }, 350);
    }
    function finishObjectSwitch() {
        if (!objectSwitching) return;
        objectSwitching = false; clearTimeout(objectSwitchTimer); objectPanoReady = true;
        renderWebElementMarkers(); setPanoramaStatus('Panorama siap. Tarik pin untuk memindahkan WebElement.');
    }
    // Pindah panorama di player yang sama (transisi crossdissolve bawaan pano.xml) tanpa memuat ulang konfigurasi, supaya mulus tanpa layar "Memuat…".
    function switchObjectPanorama() {
        objectSwitching = true; objectPanoReady = false; objectPanoPlayer.removeHotspots();
        setPanoramaStatus('Berpindah panorama…');
        clearTimeout(objectSwitchTimer); objectSwitchTimer = setTimeout(finishObjectSwitch, 2000);
        objectPanoPlayer.openNext('{' + panoramaSelect.value + '}', '');
    }
    function renderObjectForm() {
        const record = objectRecords[selectedObject];
        if (!record) { objectForm.innerHTML='<div class="object-empty">Pilih atau tambahkan object informasi.</div>'; return; }
        const input=(field,label,cls='')=>'<label class="'+cls+'"><span>'+label+'</span><input data-object-field="'+field+'" value="'+escapeObjectHtml(record[field])+'"></label>';
        const select=(field,label,values)=>'<label><span>'+label+'</span><select data-object-field="'+field+'">'+values.map(value=>'<option '+(record[field]===value?'selected':'')+'>'+value+'</option>').join('')+'</select></label>';
        const translations=Object.entries(voiceLabels).map(([code,label])=>'<label><span>'+label+'</span><textarea rows="3" data-object-lang="'+code+'">'+escapeObjectHtml(record.labelTeks?.[code]||'')+'</textarea></label>').join('');
        const previewUrl=record.infoID ? oiPreviewBaseUrl+'?info='+encodeURIComponent(record.infoID)+'&lang=id' : '';
        objectForm.innerHTML='<div class="object-form-head"><label class="object-active-toggle"><span>Object</span><span class="toggle-control"><input type="checkbox" data-object-active aria-label="Aktifkan object" '+(record.isActive?'checked':'')+'><i aria-hidden="true"></i></span></label><div class="object-form-actions"><button type="button" class="translate" data-translate-object>✦ Auto Translate</button></div></div><div class="object-detail-layout"><div><div class="object-fields">'+input('route_key','Route Key','span2')+input('title','Title')+input('nodeID','Node ID')+select('targetID','Target ID',Array.from({length:10},(_,index)=>'Element'+String(index+1).padStart(2,'0')))+input('infoID','Info ID')+select('arah','Arah',['luruskanan','luruskiri','kanan','kiri'])+input('ukuran','Ukuran')+'</div><div class="object-translations">'+translations+'</div></div><aside class="object-preview"><header><strong>Preview Info</strong><a href="'+escapeObjectHtml(previewUrl)+'" target="_blank" rel="noopener">Buka ↗</a></header>'+(previewUrl?'<iframe src="'+escapeObjectHtml(previewUrl)+'" title="Preview Object Informasi"></iframe>':'<div>Isi Info ID untuk melihat preview.</div>')+'</aside></div>';
    }
    // Klik object di daftar → editor panorama di atas langsung pindah ke panorama object itu (preview cepat).
    function showObjectPanorama(record) {
        if (!panoramaSelect || !record) return;
        const wanted = String(record.nodeID || '').replace(/[{}]/g, '').trim().toLowerCase();
        if (!wanted) return;
        const option = Array.from(panoramaSelect.options).find(item => item.value.toLowerCase() === wanted || String(item.dataset.publicNode || '').toLowerCase() === wanted);
        if (!option || panoramaSelect.value === option.value) return;
        panoramaSelect.value = option.value;
        panoramaSelect.dispatchEvent(new Event('change'));
    }
    objectList.addEventListener('click',event=>{const item=event.target.closest('[data-object]');if(item){if(Number(item.dataset.object)!==selectedObject&&objectsDirty())saveObjectData(true);selectedObject=Number(item.dataset.object);renderObjectList();showObjectPanorama(objectRecords[selectedObject]);const studio=document.getElementById('oiStageWrap');if(studio?.classList.contains('hide-right')){studio.classList.remove('hide-right');const arrow=studio.querySelector('[data-oi-collapse="right"]');if(arrow)arrow.textContent='›';}}});
    // Isi label tiap bahasa otomatis dari Title saat diketik (format "Klik untuk Informasi </br>Judul"), lalu nama diterjemahkan live per bahasa.
    // Label yang sudah diubah manual tidak ditimpa.
    const objectLabelTemplates = (title, names = {}) => {
        const name = String(title || '').trim();
        const local = code => String(names[code] || name).trim();
        return name ? {
            id: 'Klik untuk Informasi </br>' + name,
            en: 'Click for Information on </br>' + local('en'),
            zh: '点击了解</br>' + local('zh') + '的信息',
            ar: 'انقر للحصول على معلومات عن </br>' + local('ar')
        } : {};
    };
    const objectTranslateTargets = <?= json_encode(array_map(static fn(array $language): string => $language['translate'], array_filter($languageRegistry, static fn(string $code): bool => $code !== 'id', ARRAY_FILTER_USE_KEY)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const objectAutoLabels = new WeakMap(), objectTranslateTimers = new WeakMap(), objectNameCache = new Map();
    function flashObjectLabel(field) {
        field.classList.remove('label-flash'); void field.offsetWidth; field.classList.add('label-flash');
        field.addEventListener('animationend', () => field.classList.remove('label-flash'), {once:true});
    }
    // Tulis label otomatis hanya bila kolom kosong, masih berisi hasil otomatis sebelumnya, atau sama dengan templat judul lama.
    function setAutoObjectLabel(record, code, value, legacy = '') {
        const auto = objectAutoLabels.get(record) || {}; objectAutoLabels.set(record, auto);
        const current = String(record.labelTeks?.[code] || '');
        if (current.trim() !== '' && current !== (auto[code] ?? '') && current !== legacy) return false;
        record.labelTeks = record.labelTeks || {};
        record.labelTeks[code] = value; auto[code] = value;
        const field = record === objectRecords[selectedObject] ? objectForm.querySelector('[data-object-lang="' + code + '"]') : null;   // jangan menulis ke form object lain bila pengguna sudah pindah
        if (field && field.value !== value) { field.value = value; return field; }
        return true;
    }
    async function translateObjectName(name, code) {
        const key = code + '|' + name;
        if (objectNameCache.has(key)) return objectNameCache.get(key);
        const response = await fetch('https://api.mymemory.translated.net/get?q=' + encodeURIComponent(name) + '&langpair=id|' + objectTranslateTargets[code]);
        const result = await response.json();
        const text = String(result.responseData?.translatedText || '').trim();
        if (!response.ok || !text || /MYMEMORY WARNING|QUERY LENGTH LIMIT/i.test(text)) throw new Error('gagal');
        objectNameCache.set(key, text); return text;
    }
    function scheduleLiveTranslate(record) {
        clearTimeout(objectTranslateTimers.get(record));
        const name = String(record.title || '').trim(); if (!name) return;
        objectTranslateTimers.set(record, setTimeout(async () => {
            const codes = Object.keys(objectTranslateTargets).filter(code => code in objectLabelTemplates(name));
            const fields = {}; codes.forEach(code => { const field = objectForm.querySelector('[data-object-lang="' + code + '"]'); if (field && record === objectRecords[selectedObject]) { fields[code] = field; field.classList.add('is-translating'); } });
            const names = {};
            await Promise.all(codes.map(async code => { try { names[code] = await translateObjectName(name, code); } catch (_) {} }));
            Object.values(fields).forEach(field => field.classList.remove('is-translating'));
            if (String(record.title || '').trim() !== name) return;   // judul berubah lagi saat menunggu → hasil basi dibuang
            const templates = objectLabelTemplates(name, names), raw = objectLabelTemplates(name);
            codes.forEach(code => { if (!(code in names)) return; const written = setAutoObjectLabel(record, code, templates[code], raw[code]); if (written && written !== true) flashObjectLabel(written); });
        }, 650));
    }
    function autoFillObjectLabels(record, previousTitle) {
        const next = objectLabelTemplates(record.title), previous = objectLabelTemplates(previousTitle);
        Object.keys(voiceLabels).forEach(code => {
            if (!(code in next) && !(code in previous)) return;
            setAutoObjectLabel(record, code, next[code] ?? '', previous[code] ?? '');
        });
        scheduleLiveTranslate(record);
    }
    objectSearch.addEventListener('input',renderObjectList);
    objectForm.addEventListener('input',event=>{const record=objectRecords[selectedObject];if(!record)return;if(event.target.dataset.objectField||event.target.dataset.objectLang||event.target.hasAttribute('data-object-active'))trackTyping(event.target.hasAttribute('data-object-active')?'Ubah status object':'Edit object');if(event.target.dataset.objectField){const previousTitle=record.title;record[event.target.dataset.objectField]=event.target.value;if(event.target.dataset.objectField==='title')autoFillObjectLabels(record,previousTitle);}if(event.target.dataset.objectLang)record.labelTeks[event.target.dataset.objectLang]=event.target.value;if(event.target.hasAttribute('data-object-active'))record.isActive=event.target.checked;if(event.target.hasAttribute('data-object-active')){renderObjectItems(true);return;}const item=objectList.querySelector('[data-object="'+selectedObject+'"]');if(item){item.querySelector('strong').textContent=objectDisplayName(record);item.querySelector('small').textContent=(record.route_key||'')+' · '+(record.nodeID||'Tanpa node');}});
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
    });
    panoramaSelect?.addEventListener('change',()=>{selectedWebElement=-1;syncDeleteButton();if(objectPanoPlayer&&(objectPanoReady||objectSwitching)){switchObjectPanorama();return;}document.getElementById('objectPanoramaLoading').hidden=false;ensureObjectPanoPlayer().catch(error=>setPanoramaStatus('Gagal: '+error.message));});
    document.getElementById('addWebElement')?.addEventListener('click',()=>{if(!objectPanoReady)return setPanoramaStatus('Tunggu panorama selesai dimuat.');const used=editableWebElements.filter(item=>String(item.panoNodeId).toLowerCase()===panoramaSelect.value.toLowerCase()).map(item=>String(item.targetID).toLowerCase());const historyBefore=historySnapshot();let number=1,targetID;do{targetID='Element'+String(number++).padStart(2,'0');}while(used.includes(targetID.toLowerCase()));const element={nodeID:currentPublicNode(),panoNodeId:panoramaSelect.value,targetID,pan:Number(objectPanoPlayer.getPan()),tilt:Number(objectPanoPlayer.getTilt()),fov:31.53,width:3200,height:2400};editableWebElements.push(element);const record=blankObject();record.nodeID='{'+element.nodeID+'}';record.targetID=targetID;record.route_key=makeObjectRouteKey(element.nodeID,targetID);objectRecords.push(record);selectedObject=objectRecords.length-1;objectSearch.value='';renderObjectList();selectWebElement(editableWebElements.length-1);commitHistory(historyBefore,'Tambah '+targetID);setPanoramaStatus(targetID+' ditambahkan di tengah pandangan. Tarik pin untuk menggesernya, lalu tekan Simpan.');});
    // Hapus Objek Info = hapus pin WebElement di panorama sekaligus card object-nya (satu langkah, bisa diurungkan).
    document.getElementById('removeWebElement')?.addEventListener('click', async () => {
        let element = editableWebElements[selectedWebElement];
        const record = element ? objectRecords.find(item => recordMatchesElement(item, element)) : objectRecords[selectedObject];
        if (!element && record) element = editableWebElements.find(item => recordMatchesElement(record, item));
        if (!element && !record) return;
        const name = record ? objectDisplayName(record) : element.targetID;
        if (!await cuteConfirm({icon:'🗑️', title:'Hapus Objek Info ini?', message:'“' + name + '” akan dihapus beserta pin dan card-nya. Perubahan permanen setelah tombol Simpan ditekan; sebelum itu masih bisa diurungkan (Ctrl+Z).', action:'Ya, hapus'})) return;
        const historyBefore = historySnapshot();
        if (element) editableWebElements.splice(editableWebElements.indexOf(element), 1);
        if (record) { const at = objectRecords.indexOf(record); objectRecords.splice(at, 1); selectedObject = Math.max(0, Math.min(at, objectRecords.length - 1)); }
        selectedWebElement = -1; renderObjectList(); renderWebElementMarkers();
        commitHistory(historyBefore, 'Hapus Objek Info ' + name);
        setPanoramaStatus('Objek Info dihapus dari editor. Tekan Simpan untuk menerapkan, atau Urungkan (Ctrl+Z) untuk mengembalikan.');
        document.getElementById('objectProgress').textContent = '“' + name + '” dihapus · tekan ↶ Urungkan untuk mengembalikan';
    });
    // ---- Urungkan / Ulangi (Ctrl+Z / Ctrl+Y): snapshot object, WebElement, dan posisi hotspot.
    const historyUndo = [], historyRedo = [], HISTORY_LIMIT = 60;
    let typingBefore = null, typingLabel = '', typingTimer = 0;
    const undoButton = document.getElementById('oiUndo'), redoButton = document.getElementById('oiRedo');
    function historySnapshot() { return JSON.stringify({objectRecords, editableWebElements, editableObjectHotspots}); }
    function refreshHistoryButtons() {
        refreshSaveState();
        [[undoButton, historyUndo, 'Urungkan', 'Ctrl+Z', 'Tidak ada yang bisa diurungkan'], [redoButton, historyRedo, 'Ulangi', 'Ctrl+Y', 'Tidak ada yang bisa diulang']].forEach(([button, stack, verb, key, empty]) => {
            if (!button) return;
            button.disabled = !stack.length;
            button.title = stack.length ? verb + ': ' + stack[stack.length - 1].label + ' (' + key + ')' : empty;
            button.dataset.count = stack.length ? String(stack.length) : '';
        });
    }
    function flushTyping() {
        if (typingBefore === null) return;
        clearTimeout(typingTimer);
        const before = typingBefore; typingBefore = null;
        if (before !== historySnapshot()) { historyUndo.push({state:before, label:typingLabel}); if (historyUndo.length > HISTORY_LIMIT) historyUndo.shift(); historyRedo.length = 0; }
        refreshHistoryButtons();
    }
    // Ketikan beruntun digabung jadi satu langkah riwayat (jeda 1 detik).
    function trackTyping(label) {
        if (typingBefore === null) { typingBefore = historySnapshot(); typingLabel = label; }
        clearTimeout(typingTimer); typingTimer = setTimeout(flushTyping, 1000); refreshSaveState();
    }
    function commitHistory(before, label) {
        flushTyping();
        if (before === historySnapshot()) return;
        historyUndo.push({state:before, label}); if (historyUndo.length > HISTORY_LIMIT) historyUndo.shift();
        historyRedo.length = 0; refreshHistoryButtons();
    }
    function restoreHistory(state) {
        const data = JSON.parse(state), previousRecords = objectRecords;
        objectRecords = data.objectRecords; editableWebElements = data.editableWebElements; editableObjectHotspots = data.editableObjectHotspots;
        if (objectRecords.length > previousRecords.length) {   // object yang dikembalikan langsung dipilih
            const index = objectRecords.findIndex((record, i) => !previousRecords[i] || JSON.stringify(previousRecords[i]) !== JSON.stringify(record));
            selectedObject = index >= 0 ? index : objectRecords.length - 1;
        } else selectedObject = Math.max(0, Math.min(selectedObject, objectRecords.length - 1));
        selectedWebElement = -1;
        objectSearch.value = ''; renderObjectList(); renderWebElementMarkers();
    }
    function stepHistory(from, to, verb) {
        flushTyping();
        const entry = from.pop(); if (!entry) return;
        to.push({state:historySnapshot(), label:entry.label});
        restoreHistory(entry.state); refreshHistoryButtons();
        const progress = document.getElementById('objectProgress'); if (progress) progress.textContent = verb + ': ' + entry.label;
    }
    const undoHistory = () => stepHistory(historyUndo, historyRedo, '\u21b6 Dibatalkan'), redoHistory = () => stepHistory(historyRedo, historyUndo, '\u21b7 Diulang');
    undoButton?.addEventListener('click', undoHistory); redoButton?.addEventListener('click', redoHistory);
    document.addEventListener('keydown', event => {
        if (!(event.ctrlKey || event.metaKey) || event.altKey) return;
        const target = event.target, key = event.key.toLowerCase();
        if (target?.closest?.('input,textarea,select,[contenteditable="true"]')) return;   // biarkan undo teks bawaan browser di kolom isian
        if (key === 'z' && !event.shiftKey) { event.preventDefault(); undoHistory(); }
        else if (key === 'y' || (key === 'z' && event.shiftKey)) { event.preventDefault(); redoHistory(); }
    });
    // ---- Simpan: tombol, Ctrl+S, dan simpan otomatis saat berpindah object (hanya bila ada perubahan).
    let lastSavedState = historySnapshot(), savingObjects = false, savePending = false;
    const saveButton = document.getElementById('saveObjects');
    const objectsDirty = () => historySnapshot() !== lastSavedState;
    function refreshSaveState() {
        if (!saveButton) return;
        saveButton.classList.toggle('is-dirty', !savingObjects && objectsDirty());
        saveButton.classList.toggle('is-saving', savingObjects);
    }
    async function saveObjectData(auto = false) {
        if (savingObjects) { savePending = true; return; }
        if (auto && !objectsDirty()) return;
        flushTyping();
        const progress = document.getElementById('objectProgress'), sent = historySnapshot();
        savingObjects = true; if (saveButton) saveButton.disabled = true; refreshSaveState();
        progress.textContent = auto ? 'Menyimpan otomatis…' : 'Menyimpan Object Informasi, hotspot, dan WebElement…';
        try {
            const response = await fetch(location.href, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({csrf:voiceCsrf, id_lokasi:currentLocationId, records:objectRecords, webElements:editableWebElements, hotspots:editableObjectHotspots})});
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Gagal menyimpan.');
            lastSavedState = sent;
            progress.textContent = (auto ? '\u2713 Tersimpan otomatis \u00b7 ' : '\u2713 ') + result.message;
        } catch (error) { progress.textContent = (auto ? 'Gagal simpan otomatis: ' : 'Gagal: ') + error.message; }
        finally {
            savingObjects = false; if (saveButton) saveButton.disabled = false; refreshSaveState();
            if (savePending) { savePending = false; if (objectsDirty()) saveObjectData(true); }
        }
    }
    saveButton?.addEventListener('click', () => saveObjectData(false));
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && !event.altKey && !event.shiftKey && event.key.toLowerCase() === 's') { event.preventDefault(); saveObjectData(false); }
    });
    window.addEventListener('beforeunload', event => { if (objectsDirty()) { event.preventDefault(); event.returnValue = ''; } });
    // Mode studio (panorama penuh + panel melayang): tombol sembunyikan panel & pesan status berupa toast yang hilang sendiri.
    (() => {
        const wrap = document.getElementById('oiStageWrap'); if (!wrap) return;
        wrap.querySelectorAll('[data-oi-collapse]').forEach(button => button.addEventListener('click', () => {
            const side = button.dataset.oiCollapse, hidden = wrap.classList.toggle('hide-' + side);
            button.textContent = side === 'left' ? (hidden ? '›' : '‹') : (hidden ? '‹' : '›');
        }));
        // Pas satu layar: tinggi panorama = sisa tinggi jendela di bawah header, supaya halaman tidak perlu digulir.
        const stage = document.getElementById('objectPanoramaStage'), section = wrap.closest('.object-editor'), status = wrap.parentElement.querySelector('.object-panorama-status'), shell = document.querySelector('main.shell');
        let fitting = false;
        const fit = () => {
            if (fitting || !stage) return;
            const root = document.documentElement;
            if (innerWidth < 1100) { stage.style.height = ''; root.style.overflowY = ''; return; }
            const px = value => parseFloat(value) || 0, sectionStyle = getComputedStyle(section), shellStyle = shell ? getComputedStyle(shell) : null;
            const tail = px(sectionStyle.marginBottom) + (shellStyle ? px(shellStyle.paddingBottom) + px(shellStyle.marginBottom) : 0);
            const before = stage.offsetHeight, MIN = 380;
            stage.style.height = MIN + 'px';   // ukur dasar card pada tinggi minimum, lalu tambahkan seluruh sisa tinggi jendela
            const bottom = section.getBoundingClientRect().bottom + scrollY + tail;
            const height = Math.floor(MIN + innerHeight - bottom);
            stage.style.height = Math.max(MIN, height) + 'px';
            root.style.overflowY = height >= MIN ? 'hidden' : '';   // muat satu layar → kunci scroll halaman; jendela terlalu pendek → biarkan bisa digulir
            if (height >= MIN && scrollY) scrollTo(0, 0);
            if (stage.offsetHeight !== before) { fitting = true; setTimeout(() => { window.dispatchEvent(new Event('resize')); fitting = false; }, 30); }
        };
        document.body.classList.add('oi-fit');
        window.addEventListener('resize', fit); window.addEventListener('load', fit); fit(); setTimeout(fit, 400); setTimeout(fit, 1500); if (document.fonts?.ready) document.fonts.ready.then(fit);
        const toast = document.getElementById('objectProgress'); let timer = 0;
        if (toast) new MutationObserver(() => { toast.classList.add('show'); clearTimeout(timer); timer = setTimeout(() => toast.classList.remove('show'), 4500); }).observe(toast, {childList:true, characterData:true, subtree:true});
    })();
    // ---- Pratinjau di tur: tur sungguhan (skin, Smart Kit, Object Info) langsung di panorama yang sedang dibuka editor.
    (() => {
        const button = document.getElementById('previewInTour'); if (!button || !objectTourBaseUrl) { button?.remove(); return; }
        let modal = null, frame = null, titleBox = null, noteBox = null, openUrl = '';
        // objek yang jadi sasaran pandangan: pin terpilih, atau objek terpilih di daftar, atau pin pertama di panorama ini
        const targetElement = () => {
            const node = panoramaSelect.value.toLowerCase(), inPano = item => item && String(item.panoNodeId).toLowerCase() === node;
            let element = editableWebElements[selectedWebElement];
            if (!inPano(element)) { const record = objectRecords[selectedObject]; element = record ? editableWebElements.find(item => inPano(item) && recordMatchesElement(record, item)) : undefined; }
            return inPano(element) ? element : (editableWebElements.find(inPano) || null);
        };
        const buildUrl = () => {
            const element = targetElement(), url = new URL(objectTourBaseUrl, document.baseURI);
            url.search = '?cms_preview=' + Date.now();   // lewati cache index.html; pano.xml & data CMS sudah selalu diambil baru oleh tur
            url.hash = '#' + [currentPublicNode() || panoramaSelect.value].concat(element ? [Number(element.pan).toFixed(2), Number(element.tilt).toFixed(2), '60'] : []).join(',');
            return { href: url.href, element };
        };
        const close = () => { if (!modal) return; modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('oi-preview-open'); frame.src = 'about:blank'; };
        const ensureModal = () => {
            if (modal) return modal;
            modal = document.createElement('div'); modal.className = 'oi-preview-modal'; modal.setAttribute('role', 'dialog'); modal.setAttribute('aria-modal', 'true'); modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML = '<div class="oi-preview-card"><header><div class="oi-preview-title"><strong></strong><small></small></div><div class="oi-preview-actions"><button type="button" data-oi-reload><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/></svg>Muat ulang</button><a href="#" target="_blank" rel="noopener" data-oi-newtab><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>Buka di tab baru</a><button type="button" class="oi-preview-close" data-oi-close aria-label="Tutup pratinjau">\u00d7</button></div></header><iframe title="Pratinjau tur" allow="autoplay; fullscreen; gyroscope; accelerometer; magnetometer" allowfullscreen></iframe></div>';
            document.body.append(modal);
            frame = modal.querySelector('iframe'); titleBox = modal.querySelector('.oi-preview-title strong'); noteBox = modal.querySelector('.oi-preview-title small');
            modal.addEventListener('click', event => { if (event.target === modal || event.target.closest('[data-oi-close]')) close(); });
            modal.querySelector('[data-oi-reload]').addEventListener('click', () => { const next = buildUrl(); openUrl = next.href; frame.src = next.href; modal.querySelector('[data-oi-newtab]').href = next.href; });
            document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal.classList.contains('open')) close(); });
            return modal;
        };
        button.addEventListener('click', async () => {
            if (!objectPanoReady) return setPanoramaStatus('Tunggu panorama selesai dimuat.');
            // tur hanya membaca data tersimpan: simpan dulu perubahan yang belum tersimpan supaya pratinjau sesuai dengan editor
            button.disabled = true; button.classList.add('is-busy');
            try {
                for (let wait = 0; savingObjects && wait < 50; wait++) await new Promise(resolve => setTimeout(resolve, 100));
                if (objectsDirty()) await saveObjectData(true);
            } finally { button.disabled = false; button.classList.remove('is-busy'); }
            const target = buildUrl(), unsaved = objectsDirty();
            ensureModal(); openUrl = target.href; frame.src = target.href;
            const option = panoramaSelect.selectedOptions[0];
            titleBox.textContent = 'Pratinjau tur \u00b7 ' + (option ? option.textContent : panoramaSelect.value);
            noteBox.textContent = unsaved ? 'Ada perubahan yang gagal tersimpan: pratinjau menampilkan versi tersimpan terakhir.' : (target.element ? 'Langsung menghadap ' + target.element.targetID + ' \u00b7 data yang tampil sudah tersimpan.' : 'Data yang tampil sudah tersimpan.');
            noteBox.classList.toggle('is-warn', unsaved);
            modal.querySelector('[data-oi-newtab]').href = target.href;
            modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); document.body.classList.add('oi-preview-open');
        });
    })();
    renderObjectList();
    ensureObjectPanoPlayer().catch(error=>{document.getElementById('objectPanoramaLoading')?.setAttribute('hidden','');setPanoramaStatus('Gagal memuat editor panorama: '+error.message);});
}

const voiceLocation = document.getElementById('voiceLocation');

// ---- Pemutar suara gaya "now playing": pil terang saat diam → pil kaca abu saat diputar, gelombang beresonansi,
// dan kotak teks berubah jadi lirik karaoke (kata yang dibaca terisi bertahap, baris aktif di tengah) seperti Apple Music.
let voAudioContext = null, voSubtitleOn = true;
const voPlayers = new Set(), VO_BARS = 56;
const voContext = () => { if (!voAudioContext) { const Context = window.AudioContext || window.webkitAudioContext; if (Context) voAudioContext = new Context(); } return voAudioContext; };
const voFormat = seconds => { const total = Math.max(0, Math.floor(Number(seconds) || 0)); return Math.floor(total / 60) + ':' + String(total % 60).padStart(2, '0'); };
const voSentences = text => (String(text || '').replace(/\s+/g, ' ').match(/[^.!?\u3002\uff01\uff1f\u061f\u2026]+[.!?\u3002\uff01\uff1f\u061f\u2026]*/g) || []).map(part => part.trim()).filter(Boolean);
const VO_ICON_PLAY = '<svg class="vo-i-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10-6.5a1 1 0 0 0 0-1.7l-10-6.5A1 1 0 0 0 8 5.5z"/></svg>';
const VO_ICON_PAUSE = '<svg class="vo-i-pause" viewBox="0 0 24 24" aria-hidden="true"><rect x="6.5" y="5" width="4" height="14" rx="1.2"/><rect x="13.5" y="5" width="4" height="14" rx="1.2"/></svg>';
const VO_ICON_UP = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg>';

function mountVoicePlayer(root, url, textarea) {
    root.innerHTML = '<div class="vo-pill"><button type="button" class="vo-play" aria-label="Putar audio">' + VO_ICON_PLAY + VO_ICON_PAUSE + '</button>'
        + '<div class="vo-wave" role="slider" aria-label="Posisi audio" tabindex="0">' + '<i></i>'.repeat(VO_BARS) + '</div>'
        + '<span class="vo-time">0:00</span>'
        + '<button type="button" class="vo-sub is-on" aria-label="Tampilkan / sembunyikan lirik" title="Tampilkan / sembunyikan lirik">' + VO_ICON_UP + '</button></div>';
    const wrap = textarea.closest('.vo-text-wrap');
    let lyrics = wrap.querySelector('.vo-lyrics');
    if (!lyrics) { lyrics = document.createElement('div'); lyrics.className = 'vo-lyrics'; lyrics.setAttribute('aria-hidden', 'true'); lyrics.innerHTML = '<div class="vo-lyrics-clip"><div class="vo-lyrics-track"></div></div>'; wrap.append(lyrics); }
    const track = lyrics.querySelector('.vo-lyrics-track');
    const audio = new Audio(); audio.preload = 'metadata'; audio.src = url;
    const playButton = root.querySelector('.vo-play'), wave = root.querySelector('.vo-wave'), bars = Array.from(wave.children);
    const timeLabel = root.querySelector('.vo-time'), subButton = root.querySelector('.vo-sub');
    let frame = 0, lastIndex = -1, analyser = null, levelData = null, peaks = null;
    let lines = [], lineEnds = [], activeLine = -1;

    // ---- gelombang
    const heightOf = index => peaks ? 4 + peaks[index] * 26 : 5 + (Math.sin(index * 1.7) + 1) * 3;
    const paintBars = () => bars.forEach((bar, index) => bar.style.setProperty('--h', heightOf(index).toFixed(1) + 'px'));
    paintBars();
    const idle = () => { timeLabel.textContent = voFormat(audio.duration); };
    audio.addEventListener('loadedmetadata', idle);
    fetch(url).then(response => response.ok ? response.arrayBuffer() : Promise.reject()).then(buffer => { const context = voContext(); return context ? context.decodeAudioData(buffer) : Promise.reject(); }).then(decoded => {
        const channel = decoded.getChannelData(0), step = Math.max(1, Math.floor(channel.length / VO_BARS)), values = [];
        for (let index = 0; index < VO_BARS; index++) { let peak = 0; for (let i = index * step, end = Math.min(channel.length, i + step); i < end; i += 8) peak = Math.max(peak, Math.abs(channel[i])); values.push(peak); }
        const max = Math.max(...values, .001); peaks = values.map(value => Math.pow(value / max, .7)); paintBars();
    }).catch(() => {});

    // ---- lirik karaoke: tiap kalimat satu baris; isian warna bergerak MULUS (satu gradien per baris, bukan per kata) mengikuti waktu audio
    const buildLyrics = () => {
        const text = textarea.value, rtl = /[\u0600-\u06FF]/.test(text), sentences = voSentences(text);
        lyrics.dir = rtl ? 'rtl' : 'ltr'; lyrics.style.setProperty('--fill-dir', rtl ? '270deg' : '90deg');
        track.innerHTML = ''; lines = []; activeLine = -1;
        sentences.forEach(sentence => {
            const line = document.createElement('p'), span = document.createElement('span');
            line.className = 'vo-line'; span.className = 'vo-text'; span.textContent = sentence; line.append(span);
            line.style.setProperty('--p', '0%'); track.append(line); lines.push(line);
        });
        const total = sentences.reduce((sum, sentence) => sum + sentence.length + 1, 0) || 1; let run = 0;
        lineEnds = sentences.map(sentence => (run += sentence.length + 1) / total);   // batas akhir tiap baris (pecahan 0..1 dari durasi)
    };
    const centerLine = index => {
        const line = lines[index]; if (!line) return;
        track.style.transform = 'translate3d(0,' + Math.round(lyrics.clientHeight / 2 - (line.offsetTop + line.offsetHeight / 2)) + 'px,0)';
    };
    const paintLyrics = progress => {
        if (!lines.length) return;
        let index = lineEnds.findIndex(end => progress <= end); if (index < 0) index = lines.length - 1;
        if (index !== activeLine) {
            lines.forEach((line, i) => { line.classList.toggle('is-active', i === index); line.classList.toggle('is-past', i < index); line.style.setProperty('--p', i < index ? '100%' : '0%'); });
            activeLine = index; centerLine(index);
        }
        const start = index > 0 ? lineEnds[index - 1] : 0, span = lineEnds[index] - start || 1;
        lines[index].style.setProperty('--p', (Math.max(0, Math.min(1, (progress - start) / span)) * 100).toFixed(2) + '%');
    };
    const syncLyrics = () => wrap.classList.toggle('is-lyrics', root.classList.contains('is-playing') && voSubtitleOn);
    root._voSync = syncLyrics;

    // ---- pemutaran
    const tick = () => {
        const duration = audio.duration || 0, progress = duration ? audio.currentTime / duration : 0, index = Math.min(VO_BARS, Math.floor(progress * VO_BARS));
        if (index !== lastIndex) { bars.forEach((bar, i) => bar.classList.toggle('played', i < index)); lastIndex = index; }
        timeLabel.textContent = voFormat(audio.currentTime);
        let level = 0;
        if (analyser) { analyser.getByteFrequencyData(levelData); let sum = 0; for (let i = 0; i < levelData.length; i++) sum += levelData[i]; level = sum / levelData.length / 255; }
        else level = .35 + Math.abs(Math.sin(audio.currentTime * 7)) * .3;
        root.style.setProperty('--lvl', Math.min(1, level * 1.6).toFixed(3));
        bars.forEach((bar, i) => { const near = Math.abs(i - index); bar.style.transform = near <= 4 ? 'scaleY(' + (1 + Math.min(1, level * 1.8) * (1 - near / 5) * .9).toFixed(2) + ')' : ''; });
        paintLyrics(progress);
        if (!audio.paused) frame = requestAnimationFrame(tick);
    };
    const stop = () => { if (!audio.paused) audio.pause(); };
    const connectAnalyser = () => {
        if (analyser || audio._voConnected) return; audio._voConnected = true;
        try {
            const context = voContext(); if (!context) return;
            const source = context.createMediaElementSource(audio); analyser = context.createAnalyser(); analyser.fftSize = 128; levelData = new Uint8Array(analyser.frequencyBinCount);
            source.connect(analyser); analyser.connect(context.destination);
        } catch (_) { analyser = null; }
    };
    playButton.addEventListener('click', async () => {
        if (!audio.paused) { audio.pause(); return; }
        voPlayers.forEach(other => { if (other !== stop) other(); });
        const context = voContext(); if (context && context.state === 'suspended') { try { await context.resume(); } catch (_) {} }
        connectAnalyser(); buildLyrics();
        try { await audio.play(); } catch (error) { timeLabel.textContent = 'gagal'; }
    });
    audio.addEventListener('play', () => { root.classList.add('is-playing'); subButton.classList.toggle('is-on', voSubtitleOn); syncLyrics(); requestAnimationFrame(() => { centerLine(Math.max(0, activeLine)); }); cancelAnimationFrame(frame); frame = requestAnimationFrame(tick); });
    const settle = reset => { cancelAnimationFrame(frame); root.classList.remove('is-playing'); syncLyrics(); root.style.setProperty('--lvl', '0'); bars.forEach(bar => { bar.style.transform = ''; }); if (reset) { audio.currentTime = 0; lastIndex = -1; bars.forEach(bar => bar.classList.remove('played')); } idle(); };
    audio.addEventListener('pause', () => settle(false));
    audio.addEventListener('ended', () => settle(true));
    const seekTo = clientX => { const box = wave.getBoundingClientRect(); if (audio.duration) { audio.currentTime = Math.max(0, Math.min(1, (clientX - box.left) / box.width)) * audio.duration; lastIndex = -1; tick(); if (audio.paused) cancelAnimationFrame(frame); } };
    wave.addEventListener('click', event => seekTo(event.clientX));
    wave.addEventListener('keydown', event => { if (!audio.duration) return; if (event.key === 'ArrowRight') audio.currentTime = Math.min(audio.duration, audio.currentTime + 3); else if (event.key === 'ArrowLeft') audio.currentTime = Math.max(0, audio.currentTime - 3); else return; event.preventDefault(); lastIndex = -1; tick(); });
    subButton.addEventListener('click', () => { voSubtitleOn = !voSubtitleOn; document.querySelectorAll('.vo-player').forEach(player => { player.querySelector('.vo-sub')?.classList.toggle('is-on', voSubtitleOn); player._voSync?.(); }); });
    voPlayers.add(stop);
}

function renderVoiceTexts() {
    if (!voiceLocation) return;
    const data = voiceDatabase[voiceLocation.value];
    const container = document.getElementById('voiceLanguages');
    voPlayers.forEach(function (stop) { stop(); }); voPlayers.clear();   // hentikan pemutar lama sebelum kartu dibuat ulang
    container.innerHTML = '';
    Object.entries(voiceCodes).forEach(function ([code, fileCode]) {
        const item = document.createElement('article');
        item.className = 'voice-language';
        const exists = Boolean(voiceAudioFiles[code]);
        const text = String(data?.bahasa?.[code]?.welcomeText || '');
        const hasText = Boolean(text.trim());
        const audioUrl = activeProjectBaseUrl + encodeURIComponent(activeAssetsDirectory) + '/audio/' + encodeURIComponent(voiceLocation.value) + '/' + fileCode + '.mp3?v=' + Date.now();
        item.innerHTML = '<div class="voice-language-head"><span>' + voiceLabels[code] + ' → ' + fileCode + '.mp3</span><em class="voice-audio-status '+(exists?'exists':'')+'">'+(exists?'Tersedia':'Belum ada')+'</em></div>' +
            '<div class="vo-text-wrap"><textarea rows="4" data-voice-code="' + code + '"></textarea></div>' +
            '<div class="voice-language-actions">'+(!hasText?'<button type="button" class="translate" data-add-welcome="'+code+'" '+(code==='id'?'disabled title="Isi melalui editor lokasi"':'')+'>Tambah Welcome Text</button>':'<button type="button" class="save" data-generate-voice="'+code+'">'+(exists?'Generate Ulang':'Generate VO')+'</button>')+'</div>' +
            '<div class="vo-player'+(exists?'':' is-empty')+'">'+(exists?'':'<div class="vo-pill"><span class="vo-empty">Belum ada audio — buat dengan tombol Generate</span></div>')+'</div>';
        item.querySelector('textarea').value = text;
        container.appendChild(item);
        if (exists) mountVoicePlayer(item.querySelector('.vo-player'), audioUrl, item.querySelector('textarea'));
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

// ---- Pemutar video gaya YouTube berwarna hijau: tombol play besar, bilah kontrol (progres, volume, kecepatan, layar penuh), kontrol menyembunyi otomatis
function mountVideoPlayer(video) {
    const I = {
        play: '<svg viewBox="0 0 24 24"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10-6.5a1 1 0 0 0 0-1.7l-10-6.5A1 1 0 0 0 8 5.5z"/></svg>',
        pause: '<svg viewBox="0 0 24 24"><rect x="6.5" y="5" width="4" height="14" rx="1.2"/><rect x="13.5" y="5" width="4" height="14" rx="1.2"/></svg>',
        replay: '<svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 1 0 2.6-5.9"/><path d="M4 4v5h5"/></svg>',
        vol: '<svg viewBox="0 0 24 24"><path d="M4 9.5v5h3.5L12 19V5L7.5 9.5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M18 6a8.5 8.5 0 0 1 0 12"/></svg>',
        mute: '<svg viewBox="0 0 24 24"><path d="M4 9.5v5h3.5L12 19V5L7.5 9.5z"/><path d="m16 9 5 6M21 9l-5 6"/></svg>',
        full: '<svg viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>',
        exit: '<svg viewBox="0 0 24 24"><path d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"/></svg>'
    };
    video.controls = false; video.removeAttribute('controls'); video.playsInline = true; video.preload = 'metadata';
    const shell = document.createElement('div'); shell.className = 'gv-player is-paused'; shell.tabIndex = 0; shell.hidden = true;
    video.parentNode.insertBefore(shell, video); shell.append(video);
    shell.insertAdjacentHTML('beforeend',
        '<button type="button" class="gv-big" aria-label="Putar video"><span class="gv-big-btn">' + I.play + '</span></button><div class="gv-spinner" aria-hidden="true"></div><div class="gv-toast" aria-live="polite"></div>'
        + '<div class="gv-bar"><div class="gv-progress" role="slider" tabindex="0" aria-label="Posisi video"><i class="gv-buf"></i><i class="gv-played"></i><i class="gv-knob"></i><span class="gv-tip">0:00</span></div>'
        + '<div class="gv-row"><button type="button" class="gv-btn gv-play" aria-label="Putar">' + I.play + '</button>'
        + '<div class="gv-vol"><button type="button" class="gv-btn gv-mute" aria-label="Bisukan">' + I.vol + '</button><input type="range" class="gv-vol-range" min="0" max="1" step="0.05" value="1" aria-label="Volume"></div>'
        + '<span class="gv-time">0:00 / 0:00</span><span class="gv-spacer"></span>'
        + '<button type="button" class="gv-btn gv-speed" aria-label="Kecepatan putar">1x</button><button type="button" class="gv-btn gv-full" aria-label="Layar penuh">' + I.full + '</button></div></div>');
    const q = selector => shell.querySelector(selector);
    const playBtn = q('.gv-play'), bigBtn = q('.gv-big'), progress = q('.gv-progress'), played = q('.gv-played'), buf = q('.gv-buf'), knob = q('.gv-knob'), tip = q('.gv-tip');
    const timeLabel = q('.gv-time'), muteBtn = q('.gv-mute'), volRange = q('.gv-vol-range'), speedBtn = q('.gv-speed'), fullBtn = q('.gv-full'), toast = q('.gv-toast');
    const fmt = seconds => { const total = Math.max(0, Math.floor(Number(seconds) || 0)); return Math.floor(total / 60) + ':' + String(total % 60).padStart(2, '0'); };
    let frame = 0, hideTimer = 0, toastTimer = 0, dragging = false; const speeds = [1, 1.25, 1.5, 2, 0.5];

    const sync = () => { shell.hidden = !video.getAttribute('src') || video.style.display === 'none'; };
    new MutationObserver(sync).observe(video, {attributes: true, attributeFilter: ['src', 'style']}); sync();
    const say = text => { toast.textContent = text; toast.classList.add('show'); clearTimeout(toastTimer); toastTimer = setTimeout(() => toast.classList.remove('show'), 900); };
    const paint = () => {
        const duration = video.duration || 0, ratio = duration ? video.currentTime / duration : 0;
        played.style.width = (ratio * 100) + '%'; knob.style.left = (ratio * 100) + '%'; timeLabel.textContent = fmt(video.currentTime) + ' / ' + fmt(duration);
        if (video.buffered.length) buf.style.width = (Math.min(1, video.buffered.end(video.buffered.length - 1) / (duration || 1)) * 100) + '%';
        if (!video.paused && !dragging) frame = requestAnimationFrame(paint);
    };
    const toggle = () => { if (video.ended) video.currentTime = 0; video.paused ? video.play().catch(() => {}) : video.pause(); };
    const wake = () => { shell.classList.remove('idle'); clearTimeout(hideTimer); if (!video.paused) hideTimer = setTimeout(() => shell.classList.add('idle'), 2600); };

    video.addEventListener('loadedmetadata', () => { if (!video.currentTime) video.currentTime = 0.05; paint(); });   // tampilkan bingkai pertama sebagai poster
    video.addEventListener('play', () => { shell.classList.remove('is-paused', 'is-ended'); playBtn.innerHTML = I.pause; playBtn.setAttribute('aria-label', 'Jeda'); cancelAnimationFrame(frame); frame = requestAnimationFrame(paint); wake(); });
    video.addEventListener('pause', () => { shell.classList.add('is-paused'); playBtn.innerHTML = video.ended ? I.replay : I.play; playBtn.setAttribute('aria-label', 'Putar'); shell.classList.remove('idle'); paint(); });
    video.addEventListener('ended', () => { shell.classList.add('is-ended'); playBtn.innerHTML = I.replay; paint(); });
    video.addEventListener('waiting', () => shell.classList.add('is-loading')); video.addEventListener('playing', () => shell.classList.remove('is-loading')); video.addEventListener('canplay', () => shell.classList.remove('is-loading'));
    video.addEventListener('progress', paint); video.addEventListener('timeupdate', () => { if (video.paused) paint(); });
    video.addEventListener('volumechange', () => { muteBtn.innerHTML = video.muted || video.volume === 0 ? I.mute : I.vol; volRange.value = video.muted ? 0 : video.volume; });

    bigBtn.addEventListener('click', toggle); playBtn.addEventListener('click', toggle);
    video.addEventListener('click', toggle); video.addEventListener('dblclick', () => fullBtn.click());
    muteBtn.addEventListener('click', () => { video.muted = !video.muted; });
    volRange.addEventListener('input', () => { video.muted = false; video.volume = Number(volRange.value); });
    speedBtn.addEventListener('click', () => { const next = speeds[(speeds.indexOf(video.playbackRate) + 1) % speeds.length]; video.playbackRate = next; speedBtn.textContent = next + 'x'; say('Kecepatan ' + next + 'x'); });
    const fullState = () => { fullBtn.innerHTML = document.fullscreenElement === shell ? I.exit : I.full; shell.classList.toggle('is-full', document.fullscreenElement === shell); };
    fullBtn.addEventListener('click', () => { if (document.fullscreenElement) document.exitFullscreen(); else shell.requestFullscreen?.().catch(() => {}); });
    document.addEventListener('fullscreenchange', fullState);

    // progres: klik / geser untuk lompat, tooltip waktu saat disorot
    const seekFrom = clientX => { const box = progress.getBoundingClientRect(), ratio = Math.max(0, Math.min(1, (clientX - box.left) / box.width)); if (video.duration) { video.currentTime = ratio * video.duration; paint(); } };
    progress.addEventListener('pointerdown', event => { dragging = true; progress.setPointerCapture(event.pointerId); shell.classList.add('scrubbing'); seekFrom(event.clientX); });
    progress.addEventListener('pointermove', event => { const box = progress.getBoundingClientRect(), ratio = Math.max(0, Math.min(1, (event.clientX - box.left) / box.width)); tip.textContent = fmt(ratio * (video.duration || 0)); tip.style.left = (ratio * 100) + '%'; if (dragging) seekFrom(event.clientX); });
    const endDrag = () => { if (!dragging) return; dragging = false; shell.classList.remove('scrubbing'); if (!video.paused) frame = requestAnimationFrame(paint); };
    progress.addEventListener('pointerup', endDrag); progress.addEventListener('pointercancel', endDrag);

    shell.addEventListener('pointermove', wake); shell.addEventListener('pointerleave', () => { if (!video.paused) shell.classList.add('idle'); });
    shell.addEventListener('keydown', event => {
        if (event.target.matches('input')) return;
        const key = event.key.toLowerCase();
        if (key === ' ' || key === 'k') { event.preventDefault(); toggle(); say(video.paused ? 'Putar' : 'Jeda'); }
        else if (key === 'arrowright') { event.preventDefault(); video.currentTime = Math.min(video.duration || 0, video.currentTime + 5); say('+5 detik'); }
        else if (key === 'arrowleft') { event.preventDefault(); video.currentTime = Math.max(0, video.currentTime - 5); say('\u22125 detik'); }
        else if (key === 'm') { event.preventDefault(); video.muted = !video.muted; say(video.muted ? 'Senyap' : 'Suara menyala'); }
        else if (key === 'f') { event.preventDefault(); fullBtn.click(); }
        wake();
    });
}

const videoLocation = document.getElementById('videoLocation');
function refreshVideoStatus() {
    if (!videoLocation || !videoLocation.value) return;
    const path = videoLocation.selectedOptions[0].dataset.path;
    ['mobile', 'desktop'].forEach(function (type) {
        const preview = document.getElementById(type + 'Preview');
        const status = document.getElementById(type + 'Status');
        const fileName = document.getElementById(type + 'FileName');
        const urlFor = function (extension) {
            const relativeVideoPath = path.split('/').filter(Boolean).map(encodeURIComponent).join('/') + '/video/' + type + '.' + extension;
            const videoUrl = new URL(relativeVideoPath, activeProjectBaseUrl);
            videoUrl.searchParams.set('v', Date.now());
            return videoUrl.href;
        };
        const card = status.closest('.video-card'), note = document.getElementById(type + 'Note');
        const missing = function () {
            const optional = type === 'mobile';   // mobile opsional: kuning (perhatian), desktop wajib: merah
            status.textContent = optional ? 'Belum ada \u00b7 opsional' : 'Belum ada'; status.className = 'media-status ' + (optional ? 'warn' : 'missing');
            card?.classList.toggle('is-warn', optional); card?.classList.toggle('is-missing', !optional); if (note) note.hidden = !optional;
            preview.removeAttribute('src'); preview.style.display = 'none'; if (fileName) fileName.textContent = 'video/' + type + '.webm / .mp4';
        };
        // WebM diprioritaskan (sama dengan urutan di tur), lalu MP4.
        const probe = function (extensions) {
            if (!extensions.length) { missing(); return; }
            const url = urlFor(extensions[0]);
            fetch(url, { method: 'HEAD', cache: 'no-store' }).then(function (response) {
                if (!response.ok) { probe(extensions.slice(1)); return; }
                status.textContent = 'Tersedia (' + extensions[0].toUpperCase() + ')'; status.className = 'media-status exists'; preview.src = url; preview.style.display = 'block';
                card?.classList.remove('is-warn', 'is-missing'); if (note) note.hidden = true;
                if (fileName) fileName.textContent = 'video/' + type + '.' + extensions[0];
            }).catch(function () { probe(extensions.slice(1)); });
        };
        probe(['webm', 'mp4']);
    });
}
if (videoLocation) {
    videoLocation.addEventListener('change', refreshVideoStatus);
    ['mobilePreview', 'desktopPreview'].forEach(function (id) { const element = document.getElementById(id); if (element) mountVideoPlayer(element); });
    refreshVideoStatus();
    // file yang baru dipilih langsung dipratinjau di pemutar yang sama (belum diunggah); batal pilih = kembali ke video di server
    ['mobile', 'desktop'].forEach(function (type) {
        const input = document.getElementById(type + 'File'), preview = document.getElementById(type + 'Preview'), status = document.getElementById(type + 'Status'), fileName = document.getElementById(type + 'FileName');
        let objectUrl = '';
        input?.addEventListener('change', function () {
            if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = ''; }
            const file = input.files[0];
            if (!file) { refreshVideoStatus(); return; }
            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl; preview.style.display = 'block';
            status.textContent = 'Pratinjau file baru'; status.className = 'media-status pending';
            if (fileName) fileName.textContent = file.name;
            const card = status.closest('.video-card'); card?.classList.remove('is-warn', 'is-missing'); const note = document.getElementById(type + 'Note'); if (note) note.hidden = true;
            document.getElementById('videoProgress').textContent = 'Pratinjau ' + type + ': ' + file.name + ' (belum diunggah). Tekan \u201cUpload & Timpa Video\u201d untuk menerapkan.';
        });
    });
    document.getElementById('uploadVideo').addEventListener('click', async function () {
        const mobile = document.getElementById('mobileFile').files[0];
        const desktop = document.getElementById('desktopFile').files[0];
        const progress = document.getElementById('videoProgress');
        if (!mobile && !desktop) { progress.textContent = 'Pilih minimal satu file WebM atau MP4.'; return; }
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
        if (!skinPreviewFrame.getAttribute('src')) skinPreviewFrame.src = skinPreviewModal.dataset.previewUrl;
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
