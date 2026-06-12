<?php echo $header; ?>

<main class="main-content">
    <div class="fullwidth-block">
        <div class="container">
            <h1 class="section-title">Мій профіль</h1>
            <p><strong>Ім'я:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>
</main>

<?php echo $footer; ?>
