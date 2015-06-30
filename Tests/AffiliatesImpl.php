<?php

/**
 * Class AffiliatesImpl
 * @Injectable(lazy=false)
 * @Cache(enable=true)
 */
class AffiliatesImpl {
    /**
     * @Inject
     * @var \AFF\Helpers\DB
     */
    protected $db;
    protected $affiliateTableName = 'affiliates';
    protected $affiliateViewName  = 'affiliates_view';
    protected $tokenTableName     = 'token';
    protected $affiliateListView  = 'affiliate_list';
    /**
     * @Inject("partner.id")
     * @var int
     */
    protected $partnerId;
    /**
     * @Inject
     * @var \AFF\DAO\Wallet
     */
    protected $wallet;

    /**
     * @Inject
     * @var \AFF\DAO\AffiliatesTree
     */
    protected $affiliatesTree;
    /**
     * @Inject
     * @var \AFF\DAO\CommissionPlans
     */
    protected $commission;

    public function kuku($arg1) {
        return microtime(false).$arg1." === arg1 \n";
    }

    public function createAffiliate(Affiliate $affiliate, Token $token) {
        $this->db->beginTransaction();
        try {
            $affiliate->partnerId = $this->partnerId;
            $affiliate->collectRegisterIp();
            $this->db->insert($this->affiliateTableName, $affiliate->toArray());
            $affiliate->affiliateId = $this->db->getLastInsertId();
            $this->affiliatesTree->add($affiliate->affiliateId, $affiliate->getParentAffiliateId());
            $this->commission->addDefaultCommissionPlan($affiliate->affiliateId);
            $token->affiliateId = $affiliate->affiliateId;
            $this->createToken($token);
            $this->db->commit();
            return $token->affiliateId;
        } catch( DBException $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    public function getAffiliateById($affiliateId) {
        $affiliate = null;
        $affiliateData = $this->db->select($this->affiliateTableName, 'affiliateId = :affiliateId', ['affiliateId' => $affiliateId]);
        if(count($affiliateData) == 1) {
            $affiliate = new Affiliate($affiliateData[0]);
        }
        return $affiliate;
    }
    public function getAffiliateByEmail($email) {
        $affiliate = null;
        $affiliateData = $this->db->select($this->affiliateTableName, 'partnerId = :partnerId AND email = :email',
            ['email' => $email, 'partnerId' => $this->partnerId]);
        if(!empty($affiliateData) && count($affiliateData) == 1) {
            $affiliate = new Affiliate($affiliateData[0]);
        }
        return $affiliate;
    }
    public function getAffiliateByUsername($username) {
        $affiliate = null;
        $affiliateData = $this->db->select($this->affiliateTableName,
            'partnerId = :partnerId AND username = :username', ['username' => $username, 'partnerId' => $this->partnerId]);
        if(!empty($affiliateData) && count($affiliateData) == 1) {
            $affiliate = new Affiliate($affiliateData[0]);
        }
        return $affiliate;
    }
    public function getAffiliateByUsernameOrEmail($usernameOrEmail) {
        $affiliate = null;
        $affiliateData = $this->db->select($this->affiliateTableName,
            'partnerId = :partnerId AND (username = :searchString OR email = :searchString)', ['searchString' => $usernameOrEmail, 'partnerId' => $this->partnerId]);
        if(!empty($affiliateData) && count($affiliateData) == 1) {
            $affiliate = new Affiliate($affiliateData[0]);
        }
        return $affiliate;
    }
    public function getAffiliateByPromoCode($promoCode) {
        $affiliate = null;
        $affiliateData = $this->db->select($this->affiliateTableName,
            'partnerId = :partnerId AND promoCode = :promoCode', ['promoCode' => $promoCode, 'partnerId' => $this->partnerId]);
        if(!empty($affiliateData) && count($affiliateData) == 1) {
            $affiliate = new Affiliate($affiliateData[0]);
        }
        return $affiliate;
    }
    public function getToken($tokenStr) {
        $token = null;
        $tokenData = $this->db->select($this->tokenTableName, 'token = :token', ['token' => $tokenStr]);
        if(!empty($tokenData) && count($tokenData) == 1) {
            $token = new Token($tokenData[0]);
        }
        return $token;
    }
    public function getAllAffiliates() {
        return $this->db->select($this->affiliateTableName, 'partnerId = :partnerId', ['partnerId' => $this->partnerId]);
    }
    public function getAffiliatesList($start, $limit, array $filter, array $sort) {
        $where   = [ 'partnerId = :partnerId' ];
        $bind    = [
            'partnerId' => $this->partnerId
        ];
        $orderBy = [];
        $limit = $start.", ".$limit;
        //var_dump($filter);die;
        foreach($filter as $key => $val) {
            $likeVal = '%'.preg_replace('/\s+/', '%', trim($val)).'%';
            switch($key) {
                case 'affiliateId':
                    $where[] = 'affiliateId LIKE :affiliateId';
                    $bind['affiliateId'] = $likeVal;
                    break;
                case 'username':
                    $where[] = 'username LIKE :username';
                    $bind['username'] = $likeVal;
                    break;
                case 'name':
                    $where[] = 'name LIKE :name';
                    $bind['name'] = $likeVal;
                    break;
                case 'lastName':
                    $where[] = 'lastName LIKE :lastName';
                    $bind['lastName'] = $likeVal;
                    break;
                case 'email':
                    $where[] = 'email LIKE :email';
                    $bind['email'] = $likeVal;
                    break;
                case 'role':
                    $val = $likeVal;
                    if(!empty($val)) {
                        $where[] = 'role LIKE :role';
                        $bind['role'] = $val;
                    }
                    break;
                case 'status':
                    //$val = abs(intval($val));
                    if(!empty($val)) {
                        $where[] = 'status = :status';
                        $bind['status'] = $val;//intval($val);
                    }
                    break;
                case 'productId':
                    $productId = abs(intval($val));
                    if(empty($productId)) {
                        $productId = 1;
                    }
                    $where[] = 'productId = :productId';
                    $bind['productId'] = $productId;
                    break;
                case 'countryCode':
                    $where[] = 'countryCode = :countryCode';
                    $bind['countryCode'] = $val;
                    break;
                case 'commissionPlanId':
                    $planId = abs(intval($val));
                    if(!empty($planId)) {
                        $where[] = 'planId = :planId';
                        $bind['planId'] = $planId;
                    }
                    break;
            }
        }
        foreach($sort as $key => $val) {
            $val = strtolower($val) == 'desc' ? 'DESC' : 'ASC';
            $orderBy[] = $key.' '.$val;
        }
        $data = $this->db->select($this->affiliateListView, implode(' AND ', $where), $bind, '*', implode(', ', $orderBy), '', $limit, true);
        $totalRecordsCount = $this->db->foundRows();
        $this->wallet->syncWallets($data, new \DateTime());
        return new GridResult([
            'records'           => $data,
            'totalRecordsCount' => $totalRecordsCount,
        ]);
    }
    public function createToken(Token $token) {
        return $this->db->insert($this->tokenTableName, $token->toArray());
    }
    public function changePassword(Affiliate $affiliate, Token $token) {
        $this->db->beginTransaction();
        try {
            $res = $this->db->update($this->affiliateTableName, [
                'passSalt' => $affiliate->passSalt,
                'passHash' => $affiliate->passHash,
            ],
            'partnerId = :partnerId AND affiliateId = :affiliateId', [
                'partnerId'   => $this->partnerId,
                'affiliateId' => $affiliate->affiliateId,
            ]);
            $this->setTokenUsed($token->token);
            $this->db->commit();
            return $res;
        } catch( DBException $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    public function updateDetails(Affiliate $affiliate) {
        $existAffiliateByEmail = $this->getAffiliateByEmail($affiliate->email);
        if(isset($existAffiliateByEmail) && $existAffiliateByEmail->affiliateId != $affiliate->affiliateId) {
            Notification::error(1, 'This email address is already registered.', 'updateDetails');
            return false;
        } else {
            return $this->db->update($this->affiliateTableName, $affiliate->getUpdatableProperties(),
                'affiliateId = :affiliateId', ['affiliateId' => $affiliate->affiliateId]);
        }
    }
    public function verifyAffiliate(Token $token) {
        $this->db->beginTransaction();
        try {
            $res = $this->db->update($this->affiliateTableName, ['verified' => 'YES'],
                'affiliateId = :affiliateId', ['affiliateId' => $token->affiliateId]);
            $this->setTokenUsed($token->token);
            $this->db->commit();
            return $res;
        } catch( DBException $e) {
            $this->db->rollback();
            throw $e;
        }

    }
    public function isValidForRegistration(Affiliate $affiliate) {
        $isValid = $affiliate->isValidForRegistration();
        if($isValid) {
            if(!is_null($this->getAffiliateByEmail($affiliate->email))) {
                $isValid = false;
                Notification::error(1, 'This email address is already registered', 'isValidForRegistration');
            } elseif(!is_null($this->getAffiliateByUsername($affiliate->username))) {
                $isValid = false;
                Notification::error(1, 'This username is already used by another member.', 'isValidForRegistration');
            } elseif(!is_null($this->getAffiliateByPromoCode($affiliate->promoCode))) {
                $isValid = false;
                Notification::error(1, 'This promo code is already used.', 'isValidForRegistration');
            }
        }
        return $isValid;
    }
    private function setTokenUsed($tokenStr) {
        return $this->db->update($this->tokenTableName, ['used' => Token::USED_YES], 'token = :token', ['token' => $tokenStr]);
    }

    public function getAffiliateParentId($affiliateId) {
        $parentAffiliateId  = 0;
        $affiliateData = $this->db->select($this->affiliateTableName, 'affiliateId = :affiliateId', ['affiliateId' => $affiliateId]);
        if(!empty($affiliateData) && count($affiliateData) == 1) {
            $parentAffiliateId = $affiliateData[0]['parentAffiliateId'];
        }
        return $parentAffiliateId ;
    }

    public function getAffiliatesFromWallet(\DateTime $date) {
        //CALL getAffiliatesFromWallet(:date)
        $query = "SELECT
                affiliates_tree.affiliateId,
                affiliates_tree.parentAffiliateId
                FROM affiliates_tree
                INNER JOIN (SELECT
                             affiliates_tree.`left`,
                             affiliates_tree.`right`
                           FROM current_wallet_history
                           INNER JOIN affiliates_tree ON (current_wallet_history.affiliateId = affiliates_tree.affiliateId)
                           WHERE current_wallet_history.date = :date
                          ) AS t1 ON (affiliates_tree.`left` <= t1.`left` AND affiliates_tree.`right` >= t1.`right`)
                WHERE affiliates_tree.affiliateId != 0
                GROUP BY affiliates_tree.affiliateId
                ORDER BY affiliates_tree.`level` DESC";

        return $this->db->run($query, ['date' => $date->format('Y-m-d H:i:s')], ['fetch' => true]);
    }

    public function changeUserStatus($affiliateId, $userStatus) {
        $this->db->update("affiliates", ["status" => $userStatus], "affiliateId = :affiliateId", ["affiliateId" => $affiliateId]);
    }
    public function getAffiliateData($affiliateId) {
        $data = $this->db->select($this->affiliateViewName, 'affiliateId = :affiliateId', ['affiliateId' => $affiliateId]);
        if(count($data)) {
            $data = $data[0];
        } else {
            $data = [];
        }
        return $data;
    }

    public function getAffiliatesInfoByGenericNames($genericNames, $selectRows) {
        $roleCond   = implode(', ', $genericNames['role']);
        $statusCond = implode(', ', $genericNames['status']);
        $where = '';
        if(!empty($roleCond)) {
            $where .= "role IN ($roleCond)";
            if(!empty($statusCond)) {
                $where .= " AND status IN ($statusCond)";
            }
        } else {
            if(!empty($statusCond)) {
                $where .= "status IN ($statusCond)";
            }
        }

        $selectResult = $this->db->select($this->affiliateTableName, $where, [], $selectRows);

        return (count($selectRows) == 1) ? Utils::arrayColumn($selectResult, key($selectResult[0])) : $selectResult;
    }

    public function getOldToNewAffiliatesId() {
        $oldToNew = [];
        $affiliates = $this->db->select('affiliates', 'partnerId = :partnerId AND affiliate_id > 0', ['partnerId' => $this->partnerId], ['affiliate_id', 'affiliateId']);
        foreach($affiliates as $row) {
            $oldToNew[$row['affiliate_id']] = $row['affiliateId'];
        }
        return $oldToNew;
    }


    public function activateAffiliate($affiliateId) {
        $this->db->update("affiliates", ["status" => Affiliate::STATUS_ACTIVE], "affiliateId = :affiliateId", ["affiliateId" => $affiliateId]);
    }

    public function blockAffiliate($affiliateId) {
        $this->db->update("affiliates", ["status" => Affiliate::STATUS_BLOCKED], "affiliateId = :affiliateId", ["affiliateId" => $affiliateId]);
    }

    public function changeLanguage($affiliateId, $locale) {
        $this->db->update("affiliates", ["locale" => $locale], "affiliateId = :affiliateId", ["affiliateId" => $affiliateId]);
    }

    public function getAdmin() {
        return $this->db->select("affiliates", "role = :role AND partnerId = :partnerId", ["role" => Affiliate::ROLE_ADMIN, "partnerId" => $this->partnerId]);
    }

    public function updateLastLoginDate(Affiliate $affiliate) {
        $affiliate->lastLogin = date('Y-m-d H:i:s');
        return $this->db->update($this->affiliateTableName, ['lastLogin' => $affiliate->lastLogin], 'affiliateId = :affiliateId', ['affiliateId' => $affiliate->affiliateId]);
    }


}