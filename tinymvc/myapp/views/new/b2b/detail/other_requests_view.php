<?php
    if (!empty($userRequests)) {
        foreach ($userRequests as $userRequest) {
            views('new/b2b/b2b_card_view', ['request' => $userRequest]);
        }
    }
