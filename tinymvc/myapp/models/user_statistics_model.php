<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Auth Context Model.
 */
final class User_Statistics_Model extends Model
{
    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_statistic';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_STATISTICS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_user';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_user',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_user'                 => Types::INTEGER,
        'items_total'             => Types::INTEGER,
        'items_active'            => Types::INTEGER,
        'items_sold'              => Types::INTEGER,
        'items_expired'           => Types::INTEGER,
        'items_bought'            => Types::INTEGER,
        'orders_total'            => Types::INTEGER,
        'orders_active'           => Types::INTEGER,
        'orders_canceled'         => Types::INTEGER,
        'orders_finished'         => Types::INTEGER,
        'offers_received'         => Types::INTEGER,
        'offers_sent'             => Types::INTEGER,
        'offers_accepted'         => Types::INTEGER,
        'offers_finished'         => Types::INTEGER,
        'offers_declined'         => Types::INTEGER,
        'inquiries_received'      => Types::INTEGER,
        'inquiries_sent'          => Types::INTEGER,
        'inquiries_accepted'      => Types::INTEGER,
        'inquiries_declined'      => Types::INTEGER,
        'bills_total'             => Types::INTEGER,
        'bills_payment_confirmed' => Types::INTEGER,
        'item_comments_wrote'     => Types::INTEGER,
        'item_reviews_wrote'      => Types::INTEGER,
        'feedbacks_wrote'         => Types::INTEGER,
        'feedbacks_received'      => Types::INTEGER,
        'item_questions_wrote'    => Types::INTEGER,
        'item_questions_answered' => Types::INTEGER,
        'follow_users'            => Types::INTEGER,
        'followers_user'          => Types::INTEGER,
        'ep_questions_wrote'      => Types::INTEGER,
        'ep_answers_wrote'        => Types::INTEGER,
        'ep_answer_comm_wrote'    => Types::INTEGER,
        'user_photo'              => Types::INTEGER,
        'calendar_records'        => Types::INTEGER,
        'items_saved'             => Types::INTEGER,
        'user_contacts'           => Types::INTEGER,
        'company_branches'        => Types::INTEGER,
        'company_staff_users'     => Types::INTEGER,
        'company_staff_group'     => Types::INTEGER,
        'company_services'        => Types::INTEGER,
        'messages_new'            => Types::INTEGER,
        'messages_total'          => Types::INTEGER,
        'company_posts_news'      => Types::INTEGER,
        'company_posts_events'    => Types::INTEGER,
        'company_posts_updates'   => Types::INTEGER,
        'company_posts_library'   => Types::INTEGER,
        'company_posts_pictures'  => Types::INTEGER,
        'company_posts_videos'    => Types::INTEGER,
        'b2b_partners'            => Types::INTEGER,
        'b2b_requests'            => Types::INTEGER,
        'po_received'             => Types::INTEGER,
        'po_sent'                 => Types::INTEGER,
        'po_declined'             => Types::INTEGER,
        'po_accepted'             => Types::INTEGER,
        'active_featured_items'   => Types::INTEGER,
        'total_featured_items'    => Types::INTEGER,
        'active_highlight_items'  => Types::INTEGER,
        'total_highlight_items'   => Types::INTEGER,
        'estimates_accepted'      => Types::INTEGER,
        'estimates_declined'      => Types::INTEGER,
        'estimates_sent'          => Types::INTEGER,
        'estimates_received'      => Types::INTEGER,
        'estimates_finished'      => Types::INTEGER,
        'blogs_wrote'             => Types::INTEGER,
        'b2b_shippers_partners'   => Types::INTEGER,
        'dispute_finished'        => Types::INTEGER,
        'dispute_init'            => Types::INTEGER,
        'item_reviews_received'   => Types::INTEGER,
    ];

    /**
     * Relation with user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'idu');
    }
}
