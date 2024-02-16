<?php
namespace App\Config;

class TextConfig 
{
    /**alerts */
    public const ALERT_LOGIN_SUCCESS = 'Félicitations ! Vous êtes connecté !';
    public const ALERT_LOGOUT_SUCCESS = 'Vous êtes déconnecté !';
    public const ALERT_REGISTER_SUCCESS = 'Votre inscription est enregistrée ! Veuillez à présent cliquer sur le lien reçu par email afin d\'activer votre compte';
    public const ALERT_CONFIRMATION_SUCCESS = 'Votre compte est activé ! Vous pouvez désormais vous connecter';
    public const ALERT_RESET_PASSWORD = 'Un lien de réinitialisation du mot de passe vous a été envoyé par email';
    public const ALERT_RESET_PASSWORD_SUCCESS = 'Le mot de passe a été modifié avec succès !';

    public const ERROR_INVALID_CREDENTIALS = 'Identifiants invalides';
    public const ERROR_NOT_CONFIRMED_USER = 'Vous n\'avez pas vérifié votre adresse email';
    public const ERROR_RESTRICTED_USER = 'Votre compte a été restreint. Pour plus d\'informations, contactez le service client';

    public const ERROR_NOT_ENOUGH_STOCK = 'Nous sommes désolés. Stock insuffisant';
    public const ERROR_NOT_ENOUGH_QUANTITY = 'Quantité minimum atteinte';

    /**civilities */
    public const CIVILITY_M = 'Monsieur';
    public const CIVILITY_F = 'Madame';
}