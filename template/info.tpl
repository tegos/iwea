<?php echo $header; ?>

<main class="main-content">

    <div class="container">
        <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            <a itemprop="url" href="/"><span itemprop="title">Головна</span></a>
            <span itemprop="title">Список джерел</span>
        </div>
    </div>

    <div class="fullwidth-block">
        <div class="container">
            <h1 class="section-title">Список джерел</h1>

            <style>
                .sources-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .sources-table th { text-align: left; padding: 10px 14px; border-bottom: 2px solid #ddd; font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: .05em; }
                .sources-table td { padding: 12px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
                .sources-table tr:last-child td { border-bottom: none; }
                .sources-table tr.disabled td { opacity: .5; }
                .source-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; vertical-align: middle; flex-shrink: 0; }
                .source-name { display: flex; align-items: center; }
                .source-name a { font-weight: 600; text-decoration: none; color: #222; }
                .source-name a:hover { text-decoration: underline; }
                .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; letter-spacing: .03em; }
                .badge-api     { background: #e8f4fd; color: #1a6fa3; }
                .badge-scraper { background: #edf7ed; color: #2d7a2d; }
                .badge-active  { background: #edf7ed; color: #2d7a2d; }
                .badge-disabled{ background: #f5f5f5; color: #999; }
                .source-desc   { font-size: 13px; color: #555; line-height: 1.5; }
                .source-url    { font-size: 12px; color: #888; margin-top: 3px; }
            </style>

            <table class="sources-table">
                <thead>
                    <tr>
                        <th>Джерело</th>
                        <th>Тип</th>
                        <th>Опис</th>
                        <th>Країна</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($sites as $site): ?>
                <tr class="<?= $site['status'] ? '' : 'disabled' ?>">
                    <td>
                        <div class="source-name">
                            <span class="source-dot" style="background:<?= htmlspecialchars($site['color']) ?>"></span>
                            <div>
                                <a rel="nofollow" href="<?= htmlspecialchars($site['url']) ?>" target="_blank">
                                    <?= htmlspecialchars($site['name']) ?>
                                </a>
                                <div class="source-url"><?= htmlspecialchars($site['url']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($site['type'] === 'Scraper'): ?>
                            <span class="badge badge-scraper">Scraper</span>
                        <?php else: ?>
                            <span class="badge badge-api">API</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="source-desc"><?= htmlspecialchars($site['description']) ?></span></td>
                    <td><?= htmlspecialchars($site['country']) ?></td>
                    <td>
                        <?php if ($site['status']): ?>
                            <span class="badge badge-active">Активний</span>
                        <?php else: ?>
                            <span class="badge badge-disabled">Вимкнено</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

</main>

<?php echo $footer; ?>
