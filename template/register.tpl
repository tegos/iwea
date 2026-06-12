<?php echo $header; ?>

<main class="main-content">

    <div class="container">
        <div class="breadcrumb">
            <a href="/">Головна</a>
            <span>Реєстрація</span>
        </div>
    </div>


    <?php if ($error): ?>
    <div class="container">
        <div class="alert alert-danger">
            <div class="pull-right">
                <p class="close">&times;</p>
            </div>
            <strong>Помилка!</strong>
            <?php echo $error['message']; ?>
        </div>
    </div>
    <?php endif; ?>


    <div class="fullwidth-block">
        <div class="container">

            <div class="col-md-6">
                <h2 class="section-title">Реєстрація</h2>
                <p>Якщо Ви вже зареєстровані, перейдіть на сторінку
                    <a href="/login">Авторизації</a></p>
                <form action="/register" method="post" class="contact-form">
                    <div class="row">
                        <div class="col-md-8">
                            <input name="email" type="email" placeholder="Ваш e-mail..."></div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <input name="pass" type="password" placeholder="Ваш пароль..."></div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <input name="name" type="text" placeholder="Ваше ім'я..."></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <input type="submit" value="Зареєструватись">
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>


</main>

<?php echo $footer; ?>