<?php
class WheelModel extends AbstractModel {

    const TTL_TURNS = 86400;
    const TTL_REWARD = 86400;

    private static $prizes = [
        [
            'type' => 'fixed',
            'name' => '红包',
            'amount' => 5,
            'unit' => '元',
        ],
        [
            'type' => 'random',
            'name' => '随机红包',
            'amount' => 0,
            'unit' => '',
        ],
        [
            'type' => 'fixed',
            'name' => '红包',
            'amount' => 10,
            'unit' => '元',
        ],
        [
            'type' => 'random',
            'name' => '随机红包',
            'amount' => 0,
            'unit' => '',
        ],
        [
            'type' => 'card',
            'name' => '任务加倍卡',
            'amount' => 1,
            'unit' => '张',
        ],
        [
            'type' => 'fixed',
            'name' => '红包',
            'amount' => 20,
            'unit' => '元',
        ],
        [
            'type' => 'random',
            'name' => '随机红包',
            'amount' => 0,
            'unit' => '',
        ],
        [
            'type' => 'turns',
            'name' => '机会',
            'amount' => Constants::WHEEL_TURNS_REWARD,
            'unit' => '次',
        ],
    ];

    public function fetchPrizes() {
        return self::$prizes;
    }

    public function turn($uid) {
        $left_turns = $this->incrTurns($uid);
        if ($left_turns <= 0) {
            return false;
        }
        $num_turns = Constants::WHEEL_TURNS_MAX - $left_turns;
        switch ($num_turns) {
            case 3:
            case 4:
            case 5:
                if ($this->hasReward($uid, 'card')) {
                    return $this->genRandom($uid);
                }
                $bingo = rand(1, Constants::WHEEL_CARD_RATE) == 1;
                if ($bingo || $num_turns == 5) {
                    $this->reward($uid, 'card');
                    return $this->genCard($uid);
                }
                return $this->genRandom($uid);
            case 8:
            case 9:
            case 10:
                if ($this->hasReward($uid, 'turns')) {
                    return $this->genRandom($uid);
                }
                $bingo = rand(1, Constants::WHEEL_TURNS_RATE) == 1;
                if ($bingo) {
                    $this->reward($uid, 'turns');
                    return $this->genTurns($uid);
                }
                return $this->genRandom($uid);
            default:
                return $this->genRandom($uid);
        }
    }

    public function hasReward($uid, $type) {
        $redis = new Predis\Client();
        $key = $this->getRewardKey($uid, $type);
        return (bool) $redis->get($key);
    }

    public function removeCard($uid) {
        $redis = new Predis\Client();
        $key = $this->getRewardKey($uid, 'card');
        $redis->del($key);
    }

    private function reward($uid, $type) {
        $redis = new Predis\Client();
        $key = $this->getRewardKey($uid, $type);
        $redis->set($key, 1);
        $redis->expire($key, self::TTL_REWARD);
    }

    private function getRewardKey($uid, $type) {
        return md5('wheel_reward_'.$uid.'_'.$type.'_'.date('Ymd'));
    }

    private function genRandom($uid) {
        $amount = rand(Constants::WHEEL_RANDOM_LOW, Constants::WHEEL_RANDOM_HIGH) / Constants::WHEEL_RANDOM_RATE;
        $userModel = new UserModel();
        $user = $userModel->fetch($uid);
        $bonus = $this->fetchBonus($user->tudi_num);
        $amount = (1 + $bonus) * $amount;
        $walletModel = new WalletModel();
        $incomeModel = new IncomeModel();
        $walletModel->reward($uid, $amount);
        $incomeModel->create($uid, Constants::WHEEL_NAME, $amount);
        return [
            'type' => 'random',
            'name' => '红包',
            'amount' => $amount,
            'unit' => '元',
        ];
    }

    private function genCard($uid) {
        return [
            'type' => 'card',
            'name' => '任务加倍卡',
            'amount' => 1,
            'unit' => '张',
        ];
    }

    private function genTurns($uid) {
        $this->rewardTurns($uid, Constants::WHEEL_TURNS_REWARD);
        return [
            'type' => 'turns',
            'name' => '机会',
            'amount' => Constants::WHEEL_TURNS_REWARD,
            'unit' => '次',
        ];
    }

    public function fetchBonus($tudi_num) {
        $bonus = $tudi_num * 0.1;
        $bonus = min(Constants::WHEEL_BONUS_MAX, $bonus);
        return $bonus;
    }

    public function fetchTurns($uid) {
        $redis = new Predis\Client();
        $key = $this->getTurnKey($uid);
        $turns = (int)$redis->get($key);
        return max(0, Constants::WHEEL_TURNS_MAX - $turns);
    }

    public function incrTurns($uid) {
        $redis = new Predis\Client();
        $key = $this->getTurnKey($uid);
        $turns = (int)$redis->incr($key);
        $redis->expire($key, self::TTL_TURNS);
        return Constants::WHEEL_TURNS_MAX - $turns;
    }

    public function rewardTurns($uid, $turns) {
        $redis = new Predis\Client();
        $key = $this->getTurnKey($uid);
        $turns = (int)$redis->decrby($key, $turns);
        return Constants::WHEEL_TURNS_MAX - $turns;
    }

    private function getTurnKey($uid) {
        return md5('wheel_turn_'.$uid.'_'.date('Ymd'));
    }

}
