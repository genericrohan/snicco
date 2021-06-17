<?php


    declare(strict_types = 1);

    use Illuminate\Support\ViewErrorBag;
    use WPEmerge\Session\Session;

    /** @var ViewErrorBag $errors */

    /** @var Session $session */

?>


<form method="POST" action="<?= esc_attr($post_url) ?>" class="box">

    <?php if ($errors->has('message')) : ?>

        <div class="notification is-danger is-light">
            <?= $errors->first('message') ?>
        </div>

    <?php endif; ?>

    <?= $csrf_field ?>
    <input type="hidden" name="redirect_to"
           value="<?= esc_attr($redirect_to) ?>">
    <!--                        Username-->
    <div class="field">
        <label for="" class="label">Username or email</label>

        <div class="control has-icons-left">

            <input name="log" type="text" placeholder="e.g. bobsmith@gmail.com"
                   value="<?= esc_attr($session->getOldInput('username', '')) ?>"
                   class="input <?= $errors->count() ? 'is-danger' : '' ?>" required autocomplete="username">

            <span class="icon is-small is-left">
                                      <i class="fa fa-envelope"></i>
                                 </span>

        </div>
    </div>
    <!--                        Password-->
    <div class="field">
        <label for="" class="label">Password</label>
        <div class="control has-icons-left">
            <input name="pwd" type="password" placeholder="*******"
                   class="input <?= $errors->count() ? 'is-danger' : '' ?>" required autocomplete="current-password">
            <span class="icon is-small is-left">
                  <i class="fa fa-lock"></i>
                </span>
        </div>
    </div>
    <!--                        Remember me-->
    <div class="field">
        <label for="" class="checkbox">
            <input name="remember_me"
                   type="checkbox" <?= $session->getOldInput('remember_me', 'off') === 'on' ? 'checked' : '' ?>>
            Remember me
        </label>
    </div>
    <div class="field">
        <button id="login_button" class="button ">
            Login
        </button>
    </div>
    <a href="<?= esc_url($forgot_password) ?>" class="text-sm-left"> Forgot password?</a>
</form>

