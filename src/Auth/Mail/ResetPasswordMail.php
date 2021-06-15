<?php


    declare(strict_types = 1);


    namespace WPEmerge\Auth\Mail;

    use WPEmerge\Facade\WP;
    use WPEmerge\Mail\Mailable;
    use WPEmerge\Routing\UrlGenerator;

    class ResetPasswordMail extends Mailable
    {

        /**
         * @var \WP_User
         */
        public $user;

        public $site_name;

        /**
         * @var string
         */
        public  $magic_link;

        public $expires;

        public function __construct(\WP_User $user, string $magic_link, $expires)
        {
            $this->user = $user;
            $this->site_name = WP::siteName();
            $this->magic_link = $magic_link;
            $this->expires = $expires;
        }

        public function unique() : bool
        {
            return true;
        }

        public function build() : Mailable
        {

            return $this
                ->subject($title = sprintf( __( '[%s] Password Reset' ), WP::siteName() ) )
                ->view('password-forgot-email')
                ->with([
                    'magic_link' => $this->magic_link,
                ]);

        }



    }