<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SNEAK DUNGEON 管理画面</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #333333;
            background: #f5f6f8;
            font-family: "Segoe UI", "Yu Gothic UI", "Meiryo", sans-serif;
        }

        .header {
            border-bottom: 1px solid #d9dde3;
            background: #ffffff;
        }

        .header-inner {
            width: min(1180px, calc(100% - 32px));
            min-height: 62px;
            margin: 0 auto;
            display: flex;
            align-items: center;
        }

        .site-title {
            margin: 0;
            font-size: 17px;
            font-weight: 600;
        }

        .container {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 30px;
        }

        .description {
            margin: 8px 0 0;
            color: #666666;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .button {
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid #b9c1cc;
            border-radius: 5px;
            color: #333333;
            background: #ffffff;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font: inherit;
        }

        .button:hover {
            background: #f0f2f5;
        }

        .button.primary {
            border-color: #2878c7;
            color: #ffffff;
            background: #2878c7;
        }

        .button.primary:hover {
            background: #2168ad;
        }

        .button:disabled {
            opacity: 0.6;
            cursor: default;
        }

        .filter-panel {
            margin-bottom: 14px;
            padding: 14px 16px;
            border: 1px solid #d9dde3;
            background: #ffffff;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .filter-panel label {
            font-size: 14px;
            font-weight: 600;
        }

        .filter-panel select {
            min-width: 300px;
            height: 38px;
            padding: 0 10px;
            border: 1px solid #b9c1cc;
            border-radius: 4px;
            color: #333333;
            background: #ffffff;
            font: inherit;
        }

        .filter-note {
            margin: 0;
            color: #777777;
            font-size: 12px;
        }

        .update-info {
            margin-bottom: 14px;
            color: #666666;
            font-size: 13px;
        }

        .error-message {
            display: none;
            margin-bottom: 16px;
            padding: 12px 14px;
            border: 1px solid #e3a4a4;
            color: #9f2f2f;
            background: #fff4f4;
        }

        .error-message.show {
            display: block;
        }

        .section {
            margin-top: 26px;
        }

        .section-title {
            margin: 0 0 12px;
            font-size: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .summary-card,
        .chart-card,
        .table-card {
            border: 1px solid #d9dde3;
            background: #ffffff;
        }

        .summary-card {
            padding: 18px;
        }

        .summary-label {
            color: #666666;
            font-size: 13px;
        }

        .summary-value {
            margin-top: 8px;
            color: #222222;
            font-size: 32px;
            font-weight: 700;
        }

        .summary-unit {
            margin-left: 4px;
            color: #666666;
            font-size: 13px;
            font-weight: 400;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(300px, 1fr);
            gap: 18px;
        }

        .chart-card {
            padding: 20px;
        }

        .chart-card h2 {
            margin: 0;
            font-size: 18px;
        }

        .chart-note {
            margin: 5px 0 18px;
            color: #777777;
            font-size: 13px;
        }

        .chart-wrap {
            position: relative;
            min-height: 330px;
        }

        .tables-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(280px, 1fr);
            gap: 18px;
        }

        .table-card {
            overflow: hidden;
        }

        .table-card-header {
            padding: 16px 18px;
            border-bottom: 1px solid #d9dde3;
        }

        .table-card-header h2 {
            margin: 0;
            font-size: 18px;
        }

        .table-card-header p {
            margin: 5px 0 0;
            color: #777777;
            font-size: 13px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            padding: 12px 14px;
            border-bottom: 1px solid #e4e7eb;
            text-align: left;
            white-space: nowrap;
        }

        th {
            color: #555555;
            background: #f7f8fa;
            font-weight: 600;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .number-cell {
            text-align: right;
        }

        .empty-row {
            color: #777777;
            text-align: center;
        }

        .selected-player-row {
            background: #edf5fc;
        }

        .history-session {
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 900px) {
            .summary-grid,
            .action-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .charts-grid,
            .tables-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .summary-grid,
            .action-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                width: 100%;
            }

            .button {
                flex: 1;
            }

            .filter-panel {
                align-items: flex-start;
                flex-direction: column;
            }

            .filter-panel select {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
</head>

<body>
<header class="header">
    <div class="header-inner">
        <p class="site-title">SNEAK DUNGEON 管理画面</p>
    </div>
</header>

<main class="container">
    <section class="page-header">
        <div>
            <h1>プレイ統計</h1>
            <p class="description">
                全体・ペア・個人ごとのプレイ結果を集計して表示します。
            </p>
        </div>

        <div class="actions">
            <button id="refreshButton" class="button" type="button">
                更新
            </button>

            <a id="exportButton" class="button primary" href="/api/export">
                表示中のCSV出力
            </a>
        </div>
    </section>

    <section class="filter-panel" aria-label="表示対象の選択">
        <label for="targetSelect">表示対象</label>

        <select id="targetSelect">
            <option value="all">全体</option>
        </select>

        <p class="filter-note">
            ペアは同じセッションIDで記録された2人をまとめて集計します。
        </p>
    </section>

    <div id="updateInfo" class="update-info">
        データを読み込んでいます。
    </div>

    <div id="errorMessage" class="error-message"></div>

    <section class="summary-grid" aria-label="基本統計">
        <article class="summary-card">
            <div class="summary-label">ゲーム数</div>
            <div class="summary-value">
                <span id="gameCount">--</span>
                <span class="summary-unit">回</span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">平均クリアタイム</div>
            <div class="summary-value">
                <span id="avgClearTime">--</span>
                <span class="summary-unit">秒</span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">平均スニーク時間</div>
            <div class="summary-value">
                <span id="avgSneakTime">--</span>
                <span class="summary-unit">秒</span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-label">平均死亡回数</div>
            <div class="summary-value">
                <span id="avgDeaths">--</span>
                <span class="summary-unit">回</span>
            </div>
        </article>
    </section>

    <section class="section">
        <h2 class="section-title">1人あたりの平均行動回数</h2>

        <div class="action-grid">
            <article class="summary-card">
                <div class="summary-label">平均敵スタン回数</div>
                <div class="summary-value">
                    <span id="avgPunches">--</span>
                    <span class="summary-unit">回</span>
                </div>
            </article>

            <article class="summary-card">
                <div class="summary-label">平均チャット回数</div>
                <div class="summary-value">
                    <span id="avgChats">--</span>
                    <span class="summary-unit">回</span>
                </div>
            </article>

            <article class="summary-card">
                <div class="summary-label">平均スタミナアイテム取得数</div>
                <div class="summary-value">
                    <span id="avgStaminaItems">--</span>
                    <span class="summary-unit">個</span>
                </div>
            </article>
        </div>
    </section>

    <section class="section charts-grid">
        <article class="chart-card">
            <h2>時間帯別ゲーム数</h2>
            <p class="chart-note">
                同じセッションIDを1ゲームとして時間帯別に集計
            </p>

            <div class="chart-wrap">
                <canvas id="hourlyChart"></canvas>
            </div>
        </article>

        <article class="chart-card">
            <h2>ミッション達成分布</h2>
            <p class="chart-note">
                1ゲームで達成したミッション数を集計
            </p>

            <div class="chart-wrap">
                <canvas id="missionChart"></canvas>
            </div>
        </article>
    </section>

    <section class="section table-card">
        <div class="table-card-header">
            <h2>プレイ履歴</h2>
            <p>
                表示対象の最新100件。日時と各プレイヤーの結果を確認できます。
            </p>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>日時</th>
                    <th>プレイヤー</th>
                    <th class="number-cell">クリアタイム</th>
                    <th class="number-cell">スニーク</th>
                    <th class="number-cell">死亡</th>
                    <th class="number-cell">敵スタン</th>
                    <th class="number-cell">チャット</th>
                    <th class="number-cell">スタミナ</th>
                    <th class="number-cell">ミッション</th>
                    <th>ルーム</th>
                    <th>セッションID</th>
                </tr>
                </thead>

                <tbody id="historyTableBody">
                <tr>
                    <td class="empty-row" colspan="11">
                        読み込み中です。
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section table-card">
        <div class="table-card-header">
            <h2>プレイヤー別概要</h2>
            <p>
                プレイヤー名ごとのゲーム数、タイム、スニーク時間を表示
            </p>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>プレイヤー名</th>
                    <th class="number-cell">ゲーム数</th>
                    <th class="number-cell">ベストタイム</th>
                    <th class="number-cell">平均タイム</th>
                    <th class="number-cell">平均スニーク時間</th>
                    <th class="number-cell">平均ミッション数</th>
                </tr>
                </thead>

                <tbody id="playerSummaryTableBody">
                <tr>
                    <td class="empty-row" colspan="6">
                        読み込み中です。
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section tables-grid">
        <article class="table-card">
            <div class="table-card-header">
                <h2>ミッション別達成率</h2>
                <p>
                    表示対象のゲーム数に対する各ミッションの達成割合
                </p>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ミッション</th>
                        <th>内容</th>
                        <th class="number-cell">達成数</th>
                        <th class="number-cell">達成率</th>
                    </tr>
                    </thead>

                    <tbody id="missionRateTableBody">
                    <tr>
                        <td class="empty-row" colspan="4">
                            読み込み中です。
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="table-card">
            <div class="table-card-header">
                <h2>ルーム別ゲーム数</h2>
                <p>
                    同じセッションIDを1ゲームとしてルームごとに集計
                </p>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ルームID</th>
                        <th class="number-cell">ゲーム数</th>
                    </tr>
                    </thead>

                    <tbody id="roomStatsTableBody">
                    <tr>
                        <td class="empty-row" colspan="2">
                            読み込み中です。
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</main>

<script>
    let hourlyChart = null;
    let missionChart = null;

    const elements = {
        targetSelect: document.getElementById('targetSelect'),
        exportButton: document.getElementById('exportButton'),

        gameCount: document.getElementById('gameCount'),
        avgClearTime: document.getElementById('avgClearTime'),
        avgSneakTime: document.getElementById('avgSneakTime'),
        avgDeaths: document.getElementById('avgDeaths'),
        avgPunches: document.getElementById('avgPunches'),
        avgChats: document.getElementById('avgChats'),
        avgStaminaItems: document.getElementById('avgStaminaItems'),

        historyTableBody: document.getElementById('historyTableBody'),
        playerSummaryTableBody:
            document.getElementById('playerSummaryTableBody'),
        missionRateTableBody:
            document.getElementById('missionRateTableBody'),
        roomStatsTableBody:
            document.getElementById('roomStatsTableBody'),

        refreshButton: document.getElementById('refreshButton'),
        updateInfo: document.getElementById('updateInfo'),
        errorMessage: document.getElementById('errorMessage'),
    };

    function formatNumber(value, digits = 0) {
        if (
            value === null ||
            value === undefined ||
            Number.isNaN(Number(value))
        ) {
            return '--';
        }

        return Number(value).toLocaleString('ja-JP', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
        });
    }

    function showError(message = '') {
        elements.errorMessage.textContent = message;
        elements.errorMessage.classList.toggle(
            'show',
            message !== ''
        );
    }

    function makeTargetValue(scope, value = '') {
        if (scope === 'all') {
            return 'all';
        }

        return `${scope}:${encodeURIComponent(value)}`;
    }

    function parseTargetValue(rawValue) {
        if (!rawValue || rawValue === 'all') {
            return {
                scope: 'all',
                value: '',
            };
        }

        const separatorIndex = rawValue.indexOf(':');

        if (separatorIndex === -1) {
            return {
                scope: 'all',
                value: '',
            };
        }

        return {
            scope: rawValue.slice(0, separatorIndex),
            value: decodeURIComponent(
                rawValue.slice(separatorIndex + 1)
            ),
        };
    }

    function getSelectedValueFromData(data) {
        if (data.selected_scope === 'pair') {
            return makeTargetValue(
                'pair',
                data.selected_pair_key || ''
            );
        }

        if (data.selected_scope === 'player') {
            return makeTargetValue(
                'player',
                data.selected_player || ''
            );
        }

        return 'all';
    }

    function populateTargetSelect(data) {
        const selectedValue = getSelectedValueFromData(data);

        elements.targetSelect.innerHTML = '';

        const allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = '全体';
        elements.targetSelect.appendChild(allOption);

        const pairOptions = data.pair_options || [];

        if (pairOptions.length > 0) {
            const pairGroup = document.createElement('optgroup');
            pairGroup.label = 'ペア';

            pairOptions.forEach(pair => {
                const option = document.createElement('option');
                option.value = makeTargetValue(
                    'pair',
                    pair.key
                );
                option.textContent = pair.label;
                pairGroup.appendChild(option);
            });

            elements.targetSelect.appendChild(pairGroup);
        }

        const playerNames = data.player_names || [];

        if (playerNames.length > 0) {
            const playerGroup = document.createElement('optgroup');
            playerGroup.label = '個人';

            playerNames.forEach(playerName => {
                const option = document.createElement('option');
                option.value = makeTargetValue(
                    'player',
                    playerName
                );
                option.textContent = playerName;
                playerGroup.appendChild(option);
            });

            elements.targetSelect.appendChild(playerGroup);
        }

        elements.targetSelect.value = selectedValue;
    }

    function buildQueryParams(target) {
        const params = new URLSearchParams();

        if (target.scope === 'pair') {
            params.set('scope', 'pair');
            params.set('pair_key', target.value);
        } else if (target.scope === 'player') {
            params.set('scope', 'player');
            params.set('player_name', target.value);
        } else {
            params.set('scope', 'all');
        }

        return params;
    }

    function updateExportLink(target) {
        const params = buildQueryParams(target);

        elements.exportButton.href =
            `/api/export?${params.toString()}`;
    }

    function renderSummary(data) {
        elements.gameCount.textContent =
            formatNumber(data.game_count);

        elements.avgClearTime.textContent =
            data.avg_clear_time === null
                ? '--'
                : formatNumber(data.avg_clear_time, 2);

        elements.avgSneakTime.textContent =
            formatNumber(data.avg_sneak_time, 2);

        const averages = data.average_actions || {};

        elements.avgDeaths.textContent =
            formatNumber(averages.death_count, 2);

        elements.avgPunches.textContent =
            formatNumber(averages.punch_count, 2);

        elements.avgChats.textContent =
            formatNumber(averages.chat_count, 2);

        elements.avgStaminaItems.textContent =
            formatNumber(averages.stamina_item_count, 2);
    }

    function formatPlayedAt(value) {
        if (!value) {
            return '--';
        }

        const parts = value.split(' ');

        if (parts.length !== 2) {
            return value;
        }

        const dateParts = parts[0].split('-');

        if (dateParts.length !== 3) {
            return value;
        }

        return `${dateParts[0]}/${dateParts[1]}/${dateParts[2]} ${parts[1]}`;
    }

    function renderHistoryTable(history) {
        elements.historyTableBody.innerHTML = '';

        if (!Array.isArray(history) || history.length === 0) {
            elements.historyTableBody.innerHTML = `
                <tr>
                    <td class="empty-row" colspan="11">
                        データがありません。
                    </td>
                </tr>
            `;
            return;
        }

        history.forEach(play => {
            const row = document.createElement('tr');

            const values = [
                formatPlayedAt(play.played_at),
                play.player_name,
                play.clear_time === null
                    ? '--'
                    : `${formatNumber(play.clear_time, 2)}秒`,
                `${formatNumber(play.sneak_time, 2)}秒`,
                `${formatNumber(play.death_count)}回`,
                `${formatNumber(play.punch_count)}回`,
                `${formatNumber(play.chat_count)}回`,
                `${formatNumber(play.stamina_item_count)}個`,
                `${formatNumber(play.mission_count)}個`,
                play.room_id,
                play.session_id,
            ];

            values.forEach((value, index) => {
                const cell = document.createElement('td');
                cell.textContent = value;

                if (index >= 2 && index <= 8) {
                    cell.className = 'number-cell';
                }

                if (index === 10) {
                    cell.classList.add('history-session');
                    cell.title = value;
                }

                row.appendChild(cell);
            });

            elements.historyTableBody.appendChild(row);
        });
    }

    function renderPlayerSummaryTable(
        players,
        selectedScope,
        selectedPlayer
    ) {
        elements.playerSummaryTableBody.innerHTML = '';

        if (!Array.isArray(players) || players.length === 0) {
            elements.playerSummaryTableBody.innerHTML = `
                <tr>
                    <td class="empty-row" colspan="6">
                        データがありません。
                    </td>
                </tr>
            `;
            return;
        }

        players.forEach(player => {
            const row = document.createElement('tr');

            if (
                selectedScope === 'player' &&
                player.player_name === selectedPlayer
            ) {
                row.classList.add('selected-player-row');
            }

            const values = [
                player.player_name,
                `${formatNumber(player.play_count)}回`,
                player.best_clear_time === null
                    ? '--'
                    : `${formatNumber(player.best_clear_time, 2)}秒`,
                player.avg_clear_time === null
                    ? '--'
                    : `${formatNumber(player.avg_clear_time, 2)}秒`,
                `${formatNumber(player.avg_sneak_time, 2)}秒`,
                `${formatNumber(player.avg_mission_count, 2)}個`,
            ];

            values.forEach((value, index) => {
                const cell = document.createElement('td');
                cell.textContent = value;

                if (index > 0) {
                    cell.className = 'number-cell';
                }

                row.appendChild(cell);
            });

            elements.playerSummaryTableBody.appendChild(row);
        });
    }

    function renderMissionRateTable(missions) {
        elements.missionRateTableBody.innerHTML = '';

        if (
            !Array.isArray(missions) ||
            missions.length === 0
        ) {
            elements.missionRateTableBody.innerHTML = `
                <tr>
                    <td class="empty-row" colspan="4">
                        データがありません。
                    </td>
                </tr>
            `;
            return;
        }

        missions.forEach(mission => {
            const row = document.createElement('tr');

            const missionCell = document.createElement('td');
            missionCell.textContent =
                `ミッション${mission.mission_number}`;

            const nameCell = document.createElement('td');
            nameCell.textContent = mission.name;

            const completedCell = document.createElement('td');
            completedCell.className = 'number-cell';
            completedCell.textContent =
                `${formatNumber(mission.completed_count)}回`;

            const rateCell = document.createElement('td');
            rateCell.className = 'number-cell';
            rateCell.textContent =
                `${formatNumber(mission.completion_rate, 1)}%`;

            row.append(
                missionCell,
                nameCell,
                completedCell,
                rateCell
            );

            elements.missionRateTableBody.appendChild(row);
        });
    }

    function renderRoomStatsTable(roomStats) {
        elements.roomStatsTableBody.innerHTML = '';

        if (
            !Array.isArray(roomStats) ||
            roomStats.length === 0
        ) {
            elements.roomStatsTableBody.innerHTML = `
                <tr>
                    <td class="empty-row" colspan="2">
                        データがありません。
                    </td>
                </tr>
            `;
            return;
        }

        roomStats.forEach(room => {
            const row = document.createElement('tr');

            const roomCell = document.createElement('td');
            roomCell.textContent = room.room_id;

            const countCell = document.createElement('td');
            countCell.className = 'number-cell';
            countCell.textContent =
                `${formatNumber(room.play_count)}回`;

            row.append(roomCell, countCell);

            elements.roomStatsTableBody.appendChild(row);
        });
    }

    function renderCharts(data) {
        if (hourlyChart) {
            hourlyChart.destroy();
        }

        if (missionChart) {
            missionChart.destroy();
        }

        hourlyChart = new Chart(
            document.getElementById('hourlyChart'),
            {
                type: 'bar',

                data: {
                    labels: data.time_range_stats.map(
                        item => `${item.hour}時`
                    ),

                    datasets: [{
                        label: 'ゲーム数',
                        data: data.time_range_stats.map(
                            item => item.play_count
                        ),
                        borderColor: '#2878c7',
                        backgroundColor: '#8db9e5',
                        borderWidth: 1,
                    }],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            display: false,
                        },
                    },

                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },

                            ticks: {
                                color: '#666666',
                                maxRotation: 0,
                                autoSkip: true,
                            },
                        },

                        y: {
                            beginAtZero: true,

                            ticks: {
                                color: '#666666',
                                precision: 0,
                            },

                            grid: {
                                color: '#e4e7eb',
                            },
                        },
                    },
                },
            }
        );

        missionChart = new Chart(
            document.getElementById('missionChart'),
            {
                type: 'doughnut',

                data: {
                    labels: data.mission_distribution.map(
                        item => `${item.mission_count}個`
                    ),

                    datasets: [{
                        data: data.mission_distribution.map(
                            item => item.play_count
                        ),
                        backgroundColor: [
                            '#bfc5cc',
                            '#6da7dc',
                            '#72bd8c',
                            '#e3b75b',
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    }],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '58%',

                    plugins: {
                        legend: {
                            position: 'bottom',

                            labels: {
                                color: '#444444',
                                padding: 16,
                            },
                        },
                    },
                },
            }
        );
    }

    function getTargetLabel(data) {
        if (data.selected_scope === 'player') {
            return `個人：${data.selected_player}`;
        }

        if (data.selected_scope === 'pair') {
            const pair = (data.pair_options || []).find(
                item => item.key === data.selected_pair_key
            );

            return pair
                ? `ペア：${pair.label}`
                : 'ペア';
        }

        return '全体';
    }

    async function loadDashboard() {
        const selectedTarget = parseTargetValue(
            elements.targetSelect.value
        );

        elements.refreshButton.disabled = true;
        elements.targetSelect.disabled = true;
        elements.updateInfo.textContent =
            'データを読み込んでいます。';

        showError();

        try {
            const params = buildQueryParams(selectedTarget);

            const response = await fetch(
                `/api/stats?${params.toString()}`,
                {
                    headers: {
                        Accept: 'application/json',
                    },
                    cache: 'no-store',
                }
            );

            if (!response.ok) {
                throw new Error(
                    `データの取得に失敗しました（HTTP ${response.status}）`
                );
            }

            const data = await response.json();

            populateTargetSelect(data);

            const currentTarget = parseTargetValue(
                elements.targetSelect.value
            );

            updateExportLink(currentTarget);
            renderSummary(data);
            renderCharts(data);
            renderHistoryTable(data.play_history);

            renderPlayerSummaryTable(
                data.player_summaries,
                data.selected_scope,
                data.selected_player
            );

            renderMissionRateTable(
                data.mission_completion_rates
            );

            renderRoomStatsTable(
                data.room_stats
            );

            const time = new Date().toLocaleTimeString(
                'ja-JP',
                {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                }
            );

            elements.updateInfo.textContent =
                `表示対象：${getTargetLabel(data)}　最終更新：${time}`;
        } catch (error) {
            console.error(error);

            elements.updateInfo.textContent =
                'データを取得できませんでした。';

            showError(
                error.message ||
                'データの読み込みに失敗しました。'
            );
        } finally {
            elements.refreshButton.disabled = false;
            elements.targetSelect.disabled = false;
        }
    }

    elements.refreshButton.addEventListener(
        'click',
        loadDashboard
    );

    elements.targetSelect.addEventListener(
        'change',
        loadDashboard
    );

    loadDashboard();
</script>
</body>
</html>
