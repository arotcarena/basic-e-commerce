export class SiteConfig {
    
    /**purchase status */
    static STATUS_PENDING = 'status_pending';
    static STATUS_PAID = 'status_paid';
    static STATUS_SENT = 'status_sent';
    static STATUS_DELIVERED = 'status_delivered';
    static STATUS_CANCELED = 'status_canceled';
    static STATUS_LABELS = {
        [SiteConfig.STATUS_PENDING]: 'En attente',
        [SiteConfig.STATUS_PAID]: 'Payée',
        [SiteConfig.STATUS_SENT]: 'Envoyée',
        [SiteConfig.STATUS_DELIVERED]: 'Livrée',
        [SiteConfig.STATUS_CANCELED]: 'Annulée'
    };


    /** review status */
    static MODERATION_STATUS_PENDING = 'moderation_status_pending';
    static MODERATION_STATUS_PENDING_LABEL = 'En attente';
    static MODERATION_STATUS_ACCEPTED = 'moderation_status_accepted';
    static MODERATION_STATUS_ACCEPTED_LABEL = 'Accepté';
    static MODERATION_STATUS_REFUSED = 'moderation_status_refused';
    static MODERATION_STATUS_REFUSED_LABEL = 'Refusé';


}