<?php

return [
    // 遊戲站名稱
    'station_code' => 'dream_game',

    // 遊戲注單在MongoDB 裡的 collection 名稱
    'db_collection_name' => 'dream_game_tickets',

    // 特殊幣別 轉換比率
    'currency_rate' => [
        'VND2' => 1000, //越南盾 系統1000VD = 遊戲1VD
    ],

    // 遊戲種類 game_scope
    'game_scope' => [
        1 => [
            // GameId
            1 => [
                // TableID
                10101 => 'baccarat',
                10102 => 'baccarat',
                10103 => 'baccarat',
                10105 => 'baccarat',
                10106 => 'baccarat',
                10107 => 'baccarat',
                30101 => 'live_baccarat',
                30102 => 'live_baccarat',
                30103 => 'live_baccarat',
                30105 => 'live_baccarat',
                40101 => 'bobe_baccarat',
                40102 => 'bobe_baccarat',
                40103 => 'baccarat',
                50101 => 'baccarat',
                50102 => 'baccarat',
                50103 => 'baccarat',
                50105 => 'baccarat',
                50106 => 'baccarat',
                50107 => 'baccarat',
                70101 => 'baccarat',
                70102 => 'baccarat',
                70103 => 'baccarat',
                70105 => 'baccarat',
                70106 => 'baccarat',
            ],
            2 => [
                40201 => 'bobe_insurance_baccarat',
                50201 => 'insurance_baccara',
            ],
            3 => [
                10301 => 'dragon_tiger',
                30301 => 'live_dragon_tiger',
                30302 => 'live_dragon_tiger',
                50301 => 'dragon_tiger',
                50302 => 'dragon_tiger',
                50303 => 'dragon_tiger',
                50305 => 'dragon_tiger',
                70301 => 'dragon_tiger',
            ],
            4 => [
                10401 => 'roulette',
                30401 => 'live_roulette',
                50401 => 'roulette',
                70401 => 'roulette',
            ],
            5 => [
                10501 => 'dice',
                40501 => 'bobe_dice',
                50501 => 'dice',
                70501 => 'dice',
            ],
            6 => [
                50601 => 'FanTan',
            ],
            7 => [
                10701 => 'bull_fighting',
                40701 => 'bobe_bull_fighting',
                70701 => 'bull_fighting',
            ],
            8 => [
                20801 => 'compete_baccarat',
                20802 => 'compete_baccarat',
                20803 => 'compete_baccarat',
                20805 => 'compete_baccarat',
                50801 => 'compete_baccarat',
                50802 => 'compete_baccarat',
            ],
            9 => [
                30901 => 'show_hand',
            ],
            10 => [
                41001 => 'bobe_vip_baccarat',
            ],
            11 => [
                11101 => 'fried_golden_flower',
            ],
            12 => [
                11201 => 'fast_dice',
            ],
            13 => [
                51301 => 'NiuNiu',
                51302 => 'NiuNiu',
            ],
            14 => [
                71401 => 'disc',
            ],
            31 => [
                63101 => 'live_baccarat',
                63102 => 'live_baccarat',
                63103 => 'live_baccarat',
                63105 => 'live_baccarat',
                63106 => 'live_baccarat',
                63107 => 'live_baccarat',
                63108 => 'live_baccarat',
            ],
            32 => [
                63201 => 'live_dragon_tiger',
                63202 => 'live_dragon_tiger',
            ],
            33 => [
                63301 => 'live_niuniu'
            ],
        ],
        2 => [
            1 => '会员发红包',
            2 => '会员抢红包',
            3 => '小费',
            4 => '公司发红包',
            5 => '博饼记录'
        ]
    ],

    // 下注內容及開牌結果
    'game_result' => [
        'flusher' => [
            '1' => '黑桃A',
            '2' => '黑桃2',
            '3' => '黑桃3',
            '4' => '黑桃4',
            '5' => '黑桃5',
            '6' => '黑桃6',
            '7' => '黑桃7',
            '8' => '黑桃8',
            '9' => '黑桃9',
            '10' => '黑桃10',
            '11' => '黑桃J',
            '12' => '黑桃Q',
            '13' => '黑桃K',
            '14' => '紅桃A',
            '15' => '紅桃2',
            '16' => '紅桃3',
            '17' => '紅桃4',
            '18' => '紅桃5',
            '19' => '紅桃6',
            '20' => '紅桃7',
            '21' => '紅桃8',
            '22' => '紅桃9',
            '23' => '紅桃10',
            '24' => '紅桃J',
            '25' => '紅桃Q',
            '26' => '紅桃K',
            '27' => '梅花A',
            '28' => '梅花2',
            '29' => '梅花3',
            '30' => '梅花4',
            '31' => '梅花5',
            '32' => '梅花6',
            '33' => '梅花7',
            '34' => '梅花8',
            '35' => '梅花9',
            '36' => '梅花10',
            '37' => '梅花J',
            '38' => '梅花Q',
            '39' => '梅花K',
            '40' => '方塊A',
            '41' => '方塊2',
            '42' => '方塊3',
            '43' => '方塊4',
            '44' => '方塊5',
            '45' => '方塊6',
            '46' => '方塊7',
            '47' => '方塊8',
            '48' => '方塊9',
            '49' => '方塊10',
            '50' => '方塊J',
            '51' => '方塊Q',
            '52' => '方塊K',
        ],
        'baccarat' => [
            'bet' => [
                'banker' => '庄',
                'banker6' => '免佣庄',
                'player' => '闲',
                'tie' => '和',
                'bPair' => '庄对',
                'pPair' => '闲对',
                'big' => '大',
                'small' => '小',
                'bBX' => '庄保险',
                'pBX' => '闲保险'
            ],
            'win' => [
                '1' => '庄赢',
                '2' => '庄赢庄对',
                '3' => '庄赢闲对',
                '4' => '庄赢庄对闲对',
                '5' => '闲赢',
                '6' => '闲赢庄对',
                '7' => '闲赢闲对',
                '8' => '闲赢庄对闲对',
                '9' => '和赢',
                '10' => '和赢庄对',
                '11' => '和赢闲对',
                '12' => '和赢庄对闲对'
            ],
            'big_small' => [
                '1' => '小',
                '2' => '大',
            ]
        ],
        'insurance_baccara' => [
            'bet' => [
                'banker' => '庄',
                'banker6' => '免佣庄',
                'player' => '闲',
                'tie' => '和',
                'bPair' => '庄对',
                'pPair' => '闲对',
                'big' => '大',
                'small' => '小',
                'bBX' => '庄保险',
                'pBX' => '闲保险'
            ],
            'win' => [
                '1' => '庄赢',
                '2' => '庄赢庄对',
                '3' => '庄赢闲对',
                '4' => '庄赢庄对闲对',
                '5' => '闲赢',
                '6' => '闲赢庄对',
                '7' => '闲赢闲对',
                '8' => '闲赢庄对闲对',
                '9' => '和赢',
                '10' => '和赢庄对',
                '11' => '和赢闲对',
                '12' => '和赢庄对闲对'
            ],
            'big_small' => [
                '1' => '小',
                '2' => '大',
            ],
            'banker_in' => [
                '0' => '庄保险赔率为补牌前赔率',
                '1' => '庄保险赔率为补牌后赔率',
            ],
            'player_in' => [
                '0' => '闲保险赔率为补牌前赔率',
                '1' => '闲保险赔率为补牌后赔率'
            ]
        ],
        'dragon_tiger' => [
            'bet' => [
                'dragon' => '龙',
                'tiger' => '虎',
                'tie' => '和',
                'dragonRed' => '龙红',
                'dragonBlack' => '龙黑',
                'tigerRed' => '虎红',
                'tigerBlack' => '虎黑',
                'dragonOdd' => '龙单',
                'tigerOdd' => '虎单',
                'dragonEven' => '龙双',
                'tigerEven' => '虎双',
            ],
            'win' => [
                '1' => '龙赢',
                '2' => '虎赢',
                '3' => '和赢',
            ],
        ],
        'roulette' => [
            'bet' => [
                'direct' => '直注',
                'separate' => '分注',
                'street' => '街注',
                'angle' => '角注',
                'line' => '线注',
                'three' => '三数注',
                'four' => '四个号码',
                'firstRow' => '行注一',
                'sndRow' => '行注二',
                'thrRow' => '行注三',
                'firstCol' => '打注一',
                'sndCol' => '打注二',
                'thrCol' => '打注三',
                'red' => '红色',
                'black' => '黑色',
                'odd' => '单',
                'even' => '双',
                'low' => '小',
                'high' => '大'
            ]
        ],
        'dice' => [
            'bet' => [
                'big' => '大',
                'small' => '小',
                'odd' => '单',
                'even' => '双',
                'allDices' => '全围',
                'threeForces' => '三军',
                'nineWayGards' => '段牌',
                'pairs' => '长牌',
                'surroundDices' => '围骰',
                'points' => '点数'
            ]
        ],
        'bull_fighting' => [
            'bet' => [
                'player1Double' => '闲一翻倍',
                'player2Double' => '闲二翻倍',
                'player3Double' => '闲三翻倍',
                'player1Equal' => '闲一平倍',
                'player2Equal' => '闲二平倍',
                'player3Equal' => '闲三平倍',
                'player1Many' => '闲一多倍',
                'player2Many' => '闲二多倍',
                'player3Many' => '闲三多倍',
                'banker1Double' => '庄一翻倍',
                'banker2Double' => '庄二翻倍',
                'banker3Double' => '庄三翻倍',
                'banker1Equal' => '庄一平倍',
                'banker2Equal' => '庄二平倍',
                'banker3Equal' => '庄三平倍',
                'banker1Many' => '庄一多倍',
                'banker2Many' => '庄二多倍',
                'banker3Many' => '庄三多倍'
            ]
        ],
        'show_hand' => [
            'bet' => [
                'bonus' => '奖金',
                'ante' => '底注',
                'bid' => '叫牌',
                'hasBid' => '叫牌'
            ],
            'type' => [
                '1' => '无奖金',
                '2' => '一对',
                '3' => '两对',
                '4' => '三条',
                '5' => '顺子',
                '6' => '同花',
                '7' => '葫芦',
                '8' => '四条',
                '9' => '同花顺',
                '10' => '皇家同花顺',
            ],
            'win' => [
                '1' => '庄赢',
                '2' => '闲赢',
                '3' => '和局'
            ],
            'card' => [
                '1' => '高牌',
                '2' => '一对',
                '3' => '两对',
                '4' => '三条',
                '5' => '顺子',
                '6' => '同花',
                '7' => '葫芦',
                '8' => '四条',
                '9' => '同花顺',
                '10' => '皇家同花顺'
            ]
        ],
        'fried_golden_flower' => [
            'bet' => [
                'red' => '红',
                'black' => '黑',
                'luck' => '幸运一击'
            ],
            'win' => [
                '1' => '黑赢',
                '2' => '红赢',
                '3' => '和局',
            ],
            'card' => [
                '0' => '和局',
                '1' => '散牌',
                '2' => '对子(9-A)',
                '3' => '顺子',
                '4' => '金花',
                '5' => '顺金',
                '6' => '豹子',
                '7' => '豹子杀手'
            ],
            'max_card' => [
                '2' => 'A',
                '3' => '2',
                '4' => '3',
                '5' => '4',
                '6' => '5',
                '7' => '6',
                '8' => '7',
                '9' => '8',
                '10' => '9',
                '11' => '10',
                '12' => 'J',
                '13' => 'Q',
                '14' => 'K',
            ]
        ],
        'fast_dice' => [
            'bet' => [
                'big' => '大',
                'small' => '小',
                'odd' => '单',
                'even' => '双',
                'points' => '点数'
            ]
        ],
        'niuniu' => [
            'bet' => [
                '0' => '庄点数',
                '1' => '闲1点数',
                '2' => '闲2点数',
                '3' => '闲3点数',
            ],
            'win' => [
                '0' => '輸',
                '1' => '贏',
            ],
        ],
        'live_baccarat' => [
            'bet' => [
                'banker' => '庄',
                'banker6' => '免佣庄',
                'player' => '闲',
                'tie' => '和',
                'bPair' => '庄对',
                'pPair' => '闲对',
                'big' => '大',
                'small' => '小',
                'bBX' => '庄保险',
                'pBX' => '闲保险'
            ],
            'win' => [
                '1' => '庄赢',
                '2' => '庄赢庄对',
                '3' => '庄赢闲对',
                '4' => '庄赢庄对闲对',
                '5' => '闲赢',
                '6' => '闲赢庄对',
                '7' => '闲赢闲对',
                '8' => '闲赢庄对闲对',
                '9' => '和赢',
                '10' => '和赢庄对',
                '11' => '和赢闲对',
                '12' => '和赢庄对闲对'
            ],
            'big_small' => [
                '1' => '小',
                '2' => '大',
            ]
        ],
        'live_dragon_tiger' => [
            'bet' => [
                'dragon' => '龙',
                'tiger' => '虎',
                'tie' => '和',
                'dragonRed' => '龙红',
                'dragonBlack' => '龙黑',
                'tigerRed' => '虎红',
                'tigerBlack' => '虎黑',
                'dragonOdd' => '龙单',
                'tigerOdd' => '虎单',
                'dragonEven' => '龙双',
                'tigerEven' => '虎双',
            ],
            'win' => [
                '1' => '龙赢',
                '2' => '虎赢',
                '3' => '和赢',
            ],
        ],
        'disc' => [
            'bet' => [
                'zero' => '4白',
                'one' => '3白1红',
                'three' => '3红1白',
                'four' => '4红',
                'odd' => '单',
                'even' => '双',
            ],
            'win' => [
                '0' => '4白',
                '1' => '3白1红',
                '2' => '2白2红',
                '3' => '3红1白',
                '4' => '4红',
            ],
        ]
    ]
];