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
            width: min(1100px, calc(100% - 32px));
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
            width: min(1100px, calc(100% - 32px));
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
            min-width: 240px;
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

        @media (max-width: 850px) {
            .summary-grid {
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

            .summary-grid {
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
            <p class="description">全体またはプレイヤー名ごとのプレイ結果を集計して表示します。</p>
        </div>

        <div class="actions">
            <button id="refreshButton" class="button" type="button">更新</button>
            <a id="exportButton" class="button primary" href="/api/export">表示中のCSV出力</a>
        </div>
    </section>

    <section class="filter-panel" aria-label="表示対象の選択">
        <label for="playerSelect">表示対象</label>
        <select id="playerSelect">
            <option value="">全体</option>
        </select>
        <p class="filter-note">同じプレイヤー名のデータは、同一プレイヤーとしてまとめて集計します。</p>
    </section>

    <div id="updateInfo" class="update-info">データを読み込んでいます。</div>
    <div id="errorMessage" class="error-message"></div>

    <section class="summary-grid" aria-label="基本統計">
        <article class="summary-card">
            <div class="summary-label">総プレイ数</div>
            <div class="summary-value"><span id="totalPlays">--</span><span class="summary-unit">回</span></div>
        </article>

        <article class="summary-card">
            <div class="summary-label">クリア数</div>
            <div class="summary-value"><span id="clearedPlays">--</span><span class="summary-unit">回</span></div>
        </article>

        <article class="summary-card">
            <div class="summary-label">クリア率</div>
            <div class="summary-value"><span id="clearRate">--</span><span class="summary-unit">%</span></div>
        </article>

        <article class="summary-card">
            <div class="summary-label">平均クリアタイム</div>
            <div class="summary-value"><span id="avgClearTime">--</span><span class="summary-unit">秒</span></div>
        </article>
    </section>

    <section class="section">
        <h2 class="section-title">1プレイあたりの平均行動回数</h2>
        <div class="summary-grid" aria-label="平均行動回数">
            <article class="summary-card">
                <div class="summary-label">平均死亡回数</div>
                <div class="summary-value"><span id="avgDeaths">--</span><span class="summary-unit">回</span></div>
            </article>

            <article class="summary-card">
                <div class="summary-label">平均パンチ回数</div>
                <div class="summary-value"><span id="avgPunches">--</span><span class="summary-unit">回</span></div>
            </article>

            <article class="summary-card">
                <div class="summary-label">平均チャット回数</div>
                <div class="summary-value"><span id="avgChats">--</span><span class="summary-unit">回</span></div>
            </article>

            <article class="summary-card">
                <div class="summary-label">平均スタミナアイテム取得数</div>
                <div class="summary-value"><span id="avgStaminaItems">--</span><span class="summary-unit">個</span></div>
            </article>
        </div>
    </section>

    <section class="section charts-grid">
        <article class="chart-card">
            <h2>時間帯別プレイ数</h2>
            <p class="chart-note">プレイ開始時刻を0時から23時までの時間帯で集計</p>
            <div class="chart-wrap">
                <canvas id="hourlyChart"></canvas>
            </div>
        </article>

        <article class="chart-card">
            <h2>ミッション達成分布</h2>
            <p class="chart-note">1プレイで達成したミッション数を集計</p>
            <div class="chart-wrap">
                <canvas id="missionChart"></canvas>
            </div>
        </article>
    </section>

    <section class="section table-card">
        <div class="table-card-header">
            <h2>プレイヤー別概要</h2>
            <p>プレイヤー名ごとのプレイ数、クリア状況、タイム、ミッション達成数</p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>プレイヤー名</th>
                    <th class="number-cell">プレイ数</th>
                    <th class="number-cell">クリア数</th>
                    <th class="number-cell">クリア率</th>
                    <th class="number-cell">ベストタイム</th>
                    <th class="number-cell">平均タイム</th>
                    <th class="number-cell">平均ミッション数</th>
                </tr>
                </thead>
                <tbody id="playerSummaryTableBody">
                <tr>
                    <td class="empty-row" colspan="7">読み込み中です。</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="section tables-grid">
        <article class="table-card">
            <div class="table-card-header">
                <h2>ミッション別達成率</h2>
                <p>各ミッションを達成したプレイ数と、表示対象の全プレイに対する割合</p>
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
                        <td class="empty-row" colspan="4">読み込み中です。</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="table-card">
            <div class="table-card-header">
                <h2>ルーム別プレイ数</h2>
                <p>表示対象のルームIDごとの登録プレイ数</p>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ルームID</th>
                        <th class="number-cell">プレイ数</th>
                    </tr>
                    </thead>
                    <tbody id="roomStatsTableBody">
                    <tr>
                        <td class="empty-row" colspan="2">読み込み中です。</td>
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
        playerSelect: document.getElementById('playerSelect'),
        exportButton: document.getElementById('exportButton'),
        totalPlays: document.getElementById('totalPlays'),
        clearedPlays: document.getElementById('clearedPlays'),
        clearRate: document.getElementById('clearRate'),
        avgClearTime: document.getElementById('avgClearTime'),
        avgDeaths: document.getElementById('avgDeaths'),
        avgPunches: document.getElementById('avgPunches'),
        avgChats: document.getElementById('avgChats'),
        avgStaminaItems: document.getElementById('avgStaminaItems'),
        playerSummaryTableBody: document.getElementById('playerSummaryTableBody'),
        missionRateTableBody: document.getElementById('missionRateTableBody'),
        roomStatsTableBody: document.getElementById('roomStatsTableBody'),
        refreshButton: document.getElementById('refreshButton'),
        updateInfo: document.getElementById('updateInfo'),
        errorMessage: document.getElementById('errorMessage'),
    };

    function formatNumber(value, digits = 0) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '--';
        }

        return Number(value).toLocaleString('ja-JP', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
        });
    }

    function showError(message = '') {
        elements.errorMessage.textContent = message;
        elements.errorMessage.classList.toggle('show', message !== '');
    }

    function updateExportLink(playerName) {
        const params = new URLSearchParams();

        if (playerName) {
            params.set('player_name', playerName);
        }

        const query = params.toString();
        elements.exportButton.href = query ? `/api/export?${query}` : '/api/export';
    }

    function populatePlayerSelect(playerNames, selectedPlayer) {
        const currentValue = selectedPlayer || '';
        elements.playerSelect.innerHTML = '';

        const allOption = document.createElement('option');
        allOption.value = '';
        allOption.textContent = '全体';
        elements.playerSelect.appendChild(allOption);

        (playerNames || []).forEach(playerName => {
            const option = document.createElement('option');
            option.value = playerName;
            option.textContent = playerName;
            elements.playerSelect.appendChild(option);
        });

        elements.playerSelect.value = currentValue;
    }

    function renderSummary(data) {
        elements.totalPlays.textContent = formatNumber(data.total_plays);
        elements.clearedPlays.textContent = formatNumber(data.cleared_plays);
        elements.clearRate.textContent = formatNumber(data.clear_rate, 1);
        elements.avgClearTime.textContent = data.avg_clear_time === null
            ? '--'
            : formatNumber(data.avg_clear_time, 2);

        const averages = data.average_actions || {};
        elements.avgDeaths.textContent = formatNumber(averages.death_count, 2);
        elements.avgPunches.textContent = formatNumber(averages.punch_count, 2);
        elements.avgChats.textContent = formatNumber(averages.chat_count, 2);
        elements.avgStaminaItems.textContent = formatNumber(averages.stamina_item_count, 2);
    }

    function renderPlayerSummaryTable(players, selectedPlayer) {
        elements.playerSummaryTableBody.innerHTML = '';

        if (!Array.isArray(players) || players.length === 0) {
            elements.playerSummaryTableBody.innerHTML = `
                <tr><td class="empty-row" colspan="7">データがありません。</td></tr>
            `;
            return;
        }

        players.forEach(player => {
            const row = document.createElement('tr');

            if (selectedPlayer && player.player_name === selectedPlayer) {
                row.classList.add('selected-player-row');
            }

            const values = [
                player.player_name,
                `${formatNumber(player.play_count)}回`,
                `${formatNumber(player.cleared_count)}回`,
                `${formatNumber(player.clear_rate, 1)}%`,
                player.best_clear_time === null ? '--' : `${formatNumber(player.best_clear_time, 2)}秒`,
                player.avg_clear_time === null ? '--' : `${formatNumber(player.avg_clear_time, 2)}秒`,
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

        if (!Array.isArray(missions) || missions.length === 0) {
            elements.missionRateTableBody.innerHTML = `
                <tr><td class="empty-row" colspan="4">データがありません。</td></tr>
            `;
            return;
        }

        missions.forEach(mission => {
            const row = document.createElement('tr');

            const missionCell = document.createElement('td');
            missionCell.textContent = `ミッション${mission.mission_number}`;

            const nameCell = document.createElement('td');
            nameCell.textContent = mission.name;

            const completedCell = document.createElement('td');
            completedCell.className = 'number-cell';
            completedCell.textContent = `${formatNumber(mission.completed_count)}回`;

            const rateCell = document.createElement('td');
            rateCell.className = 'number-cell';
            rateCell.textContent = `${formatNumber(mission.completion_rate, 1)}%`;

            row.append(missionCell, nameCell, completedCell, rateCell);
            elements.missionRateTableBody.appendChild(row);
        });
    }

    function renderRoomStatsTable(roomStats) {
        elements.roomStatsTableBody.innerHTML = '';

        if (!Array.isArray(roomStats) || roomStats.length === 0) {
            elements.roomStatsTableBody.innerHTML = `
                <tr><td class="empty-row" colspan="2">データがありません。</td></tr>
            `;
            return;
        }

        roomStats.forEach(room => {
            const row = document.createElement('tr');

            const roomCell = document.createElement('td');
            roomCell.textContent = room.room_id;

            const countCell = document.createElement('td');
            countCell.className = 'number-cell';
            countCell.textContent = `${formatNumber(room.play_count)}回`;

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

        hourlyChart = new Chart(document.getElementById('hourlyChart'), {
            type: 'bar',
            data: {
                labels: data.time_range_stats.map(item => `${item.hour}時`),
                datasets: [{
                    label: 'プレイ数',
                    data: data.time_range_stats.map(item => item.play_count),
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
        });

        missionChart = new Chart(document.getElementById('missionChart'), {
            type: 'doughnut',
            data: {
                labels: data.mission_distribution.map(item => `${item.mission_count}個`),
                datasets: [{
                    data: data.mission_distribution.map(item => item.play_count),
                    backgroundColor: ['#bfc5cc', '#6da7dc', '#72bd8c', '#e3b75b'],
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
        });
    }

    async function loadDashboard() {
        const selectedPlayer = elements.playerSelect.value;
        elements.refreshButton.disabled = true;
        elements.playerSelect.disabled = true;
        elements.updateInfo.textContent = 'データを読み込んでいます。';
        showError();

        try {
            const params = new URLSearchParams();

            if (selectedPlayer) {
                params.set('player_name', selectedPlayer);
            }

            const query = params.toString();
            const url = query ? `/api/stats?${query}` : '/api/stats';

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(`データの取得に失敗しました（HTTP ${response.status}）`);
            }

            const data = await response.json();
            populatePlayerSelect(data.player_names, data.selected_player);
            updateExportLink(data.selected_player);
            renderSummary(data);
            renderCharts(data);
            renderPlayerSummaryTable(data.player_summaries, data.selected_player);
            renderMissionRateTable(data.mission_completion_rates);
            renderRoomStatsTable(data.room_stats);

            const time = new Date().toLocaleTimeString('ja-JP', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });

            const target = data.selected_player || '全体';
            elements.updateInfo.textContent = `表示対象：${target}　最終更新：${time}`;
        } catch (error) {
            console.error(error);
            elements.updateInfo.textContent = 'データを取得できませんでした。';
            showError(error.message || 'データの読み込みに失敗しました。');
        } finally {
            elements.refreshButton.disabled = false;
            elements.playerSelect.disabled = false;
        }
    }

    elements.refreshButton.addEventListener('click', loadDashboard);
    elements.playerSelect.addEventListener('change', loadDashboard);
    loadDashboard();
</script>
</body>
</html>
