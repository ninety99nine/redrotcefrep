<?php

namespace App\Enums;

enum WorkflowTrigger: string
{
    //  WorkflowTarget: ORDER
    case WAITING = 'waiting';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case ON_ITS_WAY = 'on its way';
    case READY_FOR_PICKUP = 'ready for pickup';

    //  WorkflowTarget: PAYMENT
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially paid';
    case WAITING_CONFIRMATION = 'waiting confirmation';

    //  WorkflowTarget: PRODUCT
    case NO_STOCK = 'no stock';
    case LOW_STOCK = 'low stock';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
