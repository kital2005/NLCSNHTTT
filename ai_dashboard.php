<?php
session_start();
require_once 'db.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$active_menu = 'ai';
$page_title = 'AI Control Center';
$is_user_admin = is_admin($conn, $current_user_id);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | SocialAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/styles.php'; ?>
    <style>
        .ai-hero { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%); border-radius: 20px; color: white; }
        .nav-pills .nav-link { border-radius: 12px; font-weight: 600; color: #64748b; }
        .nav-pills .nav-link.active { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .kpi-card { background: #fff; border: 1px solid rgba(0,0,0,0.04); }
        [data-bs-theme="dark"] .kpi-card { background: #1e293b; border-color: #334155; }
        .log-row { font-size: 0.85rem; border-bottom: 1px solid rgba(0,0,0,0.05); }
        [data-bs-theme="dark"] .log-row { border-color: #334155; }
        .test-preview { max-height: 300px; object-fit: cover; border-radius: 12px; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <div class="col-md-9">
            <div class="ai-hero p-4 mb-4 shadow">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="fa-solid fa-brain me-2"></i>AI Control Center</h3>
                        <p class="mb-0 opacity-75">Quản lý mô hình ML, dataset và pipeline sinh ảnh minh họa</p>
                    </div>
                    <div id="model-badge" class="badge bg-white text-dark fs-6 px-3 py-2">
                        <i class="fa-solid fa-spinner fa-spin me-1"></i> Đang tải...
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills mb-4 gap-2 flex-wrap" id="aiTabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-overview"><i class="fa-solid fa-chart-pie me-1"></i> Tổng quan</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-model"><i class="fa-solid fa-microchip me-1"></i> Mô hình</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-dataset"><i class="fa-solid fa-database me-1"></i> Dataset</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-image"><i class="fa-solid fa-image me-1"></i> Sinh ảnh</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-logs"><i class="fa-solid fa-list me-1"></i> Nhật ký</button></li>
            </ul>

            <div class="tab-content">
                <!-- TAB TỔNG QUAN -->
                <div class="tab-pane fade show active" id="tab-overview">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6"><div class="kpi-card shadow-sm"><div class="text-muted small">Lượt gọi AI</div><div class="fs-3 fw-bold text-primary" id="kpi-calls">-</div></div></div>
                        <div class="col-md-3 col-6"><div class="kpi-card shadow-sm"><div class="text-muted small">Accuracy</div><div class="fs-3 fw-bold text-success" id="kpi-accuracy">-</div></div></div>
                        <div class="col-md-3 col-6"><div class="kpi-card shadow-sm"><div class="text-muted small">TB xử lý</div><div class="fs-3 fw-bold text-info" id="kpi-time">-</div></div></div>
                        <div class="col-md-3 col-6"><div class="kpi-card shadow-sm"><div class="text-muted small">Tỷ lệ thành công</div><div class="fs-3 fw-bold text-purple" id="kpi-success">-</div></div></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card p-3 shadow-sm"><h6 class="fw-bold mb-3">Phân bố chủ đề</h6><canvas id="chartTopic" height="220"></canvas></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card p-3 shadow-sm"><h6 class="fw-bold mb-3">Phân bố cảm xúc</h6><canvas id="chartEmotion" height="220"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- TAB MÔ HÌNH -->
                <div class="tab-pane fade" id="tab-model">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card p-4 shadow-sm">
                                <h5 class="fw-bold mb-3">So sánh thuật toán</h5>
                                <div class="d-flex justify-content-around text-center mb-4">
                                    <div class="p-3 rounded-3 bg-primary bg-opacity-10">
                                        <div class="text-muted small">SVM</div>
                                        <div class="fs-2 fw-bold text-primary" id="svm-acc">-</div>
                                    </div>
                                    <div class="p-3 rounded-3 bg-success bg-opacity-10">
                                        <div class="text-muted small">Random Forest</div>
                                        <div class="fs-2 fw-bold text-success" id="rf-acc">-</div>
                                    </div>
                                </div>
                                <div id="model-details" class="small text-muted"></div>
                                <?php if ($is_user_admin): ?>
                                <button id="btn-retrain" class="btn btn-ai w-100 mt-3 rounded-pill py-2">
                                    <i class="fa-solid fa-rotate me-1"></i> Huấn luyện lại model
                                </button>
                                <div id="train-progress" class="d-none mt-3">
                                    <div class="progress" style="height: 8px; border-radius: 8px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                                    </div>
                                    <small class="text-muted">Đang huấn luyện, có thể mất 30-60 giây...</small>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info small mt-2 mb-0">Chỉ tài khoản admin mới có thể huấn luyện lại model.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card p-3 shadow-sm">
                                <h6 class="fw-bold mb-3">Confusion Matrix</h6>
                                <div id="cm-container" class="text-center">
                                    <img id="cm-image" src="confusion_matrix.png" class="img-fluid rounded-3" alt="Confusion Matrix" onerror="this.parentElement.innerHTML='<p class=\'text-muted py-5\'><i class=\'fa-solid fa-chart-area fa-3x opacity-25 d-block mb-2\'></i>Chưa có biểu đồ. Hãy huấn luyện model trước.</p>'">
                                </div>
                            </div>
                            <div class="card p-3 shadow-sm mt-3">
                                <h6 class="fw-bold mb-3">Lịch sử huấn luyện</h6>
                                <div id="training-history" class="small"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB DATASET -->
                <div class="tab-pane fade" id="tab-dataset">
                    <div class="card p-4 shadow-sm">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><div class="kpi-card shadow-sm text-center p-3"><div class="fs-2 fw-bold" id="ds-size">-</div><div class="text-muted small">Tổng mẫu</div></div></div>
                            <div class="col-md-4"><div class="kpi-card shadow-sm text-center p-3"><div class="fs-2 fw-bold text-primary" id="ds-topics">-</div><div class="text-muted small">Chủ đề</div></div></div>
                            <div class="col-md-4"><div class="kpi-card shadow-sm text-center p-3"><div class="fs-2 fw-bold text-success" id="ds-emotions">-</div><div class="text-muted small">Cảm xúc</div></div></div>
                        </div>
                        <h6 class="fw-bold">Pipeline dữ liệu</h6>
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-light text-dark border px-3 py-2">generate_data.py</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                            <span class="badge bg-light text-dark border px-3 py-2">dataset_20k.csv</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                            <span class="badge bg-light text-dark border px-3 py-2">TF-IDF (3000 features)</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                            <span class="badge bg-primary px-3 py-2">SVM / Random Forest</span>
                        </div>
                        <p class="text-muted small mb-0">
                            Dataset gồm 4 chủ đề: Khoa học & Công nghệ, Giải trí & Gaming, Học tập, Đời sống thường ngày.
                            Mỗi câu gắn nhãn cảm xúc tiếng Việt để model dự đoán phục vụ sinh ảnh.
                        </p>
                    </div>
                </div>

                <!-- TAB SINH ẢNH -->
                <div class="tab-pane fade" id="tab-image">
                    <div class="card p-4 shadow-sm mb-3">
                        <h5 class="fw-bold mb-3">Test sinh ảnh & phân loại</h5>
                        <textarea id="test-content" class="form-control mb-3" rows="3" placeholder="Nhập nội dung bài viết để test AI..."></textarea>
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <select id="test-provider" class="form-select" style="width: auto;">
                                <option value="pollinations">Pollinations.ai</option>
                                <option value="huggingface">Hugging Face SD v1.5</option>
                            </select>
                            <button id="btn-test-classify" class="btn btn-outline-primary rounded-pill"><i class="fa-solid fa-tags me-1"></i> Chỉ phân loại</button>
                            <button id="btn-test-full" class="btn btn-ai rounded-pill"><i class="fa-solid fa-wand-magic-sparkles me-1"></i> Phân loại + Sinh ảnh</button>
                        </div>
                        <div id="test-result" class="d-none">
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-primary" id="test-topic"></span>
                                <span class="badge bg-success" id="test-emotion"></span>
                                <span class="badge bg-secondary" id="test-time"></span>
                            </div>
                            <img id="test-image" class="test-preview w-100 d-none" alt="Test result">
                        </div>
                    </div>
                    <div class="card p-3 shadow-sm">
                        <h6 class="fw-bold mb-3">Ảnh AI gần đây từ bài viết</h6>
                        <div class="row g-2" id="recent-images"></div>
                    </div>
                </div>

                <!-- TAB NHẬT KÝ -->
                <div class="tab-pane fade" id="tab-logs">
                    <div class="card shadow-sm overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 small">
                                <thead class="table-light"><tr><th>Thời gian</th><th>User</th><th>Nội dung</th><th>Chủ đề</th><th>Cảm xúc</th><th>Thời gian xử lý</th><th>Trạng thái</th></tr></thead>
                                <tbody id="logs-table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php include 'includes/footer_scripts.php'; ?>
<script>
let topicChart, emotionChart;

async function loadStats() {
    try {
        const res = await fetch('api_ai_stats.php');
        const data = await res.json();
        if (data.status !== 'success') return;

        const s = data.stats;
        document.getElementById('kpi-calls').textContent = s.total_ai_calls;
        document.getElementById('kpi-time').textContent = (s.avg_processing_ms / 1000).toFixed(1) + 's';
        document.getElementById('kpi-success').textContent = s.success_rate + '%';

        const mi = data.model_info;
        if (mi) {
            document.getElementById('kpi-accuracy').textContent = Math.max(mi.svm_accuracy, mi.rf_accuracy) + '%';
            document.getElementById('svm-acc').textContent = mi.svm_accuracy + '%';
            document.getElementById('rf-acc').textContent = mi.rf_accuracy + '%';
            document.getElementById('model-badge').innerHTML = '<i class="fa-solid fa-trophy me-1"></i> ' + mi.best_model + ' (' + Math.max(mi.svm_accuracy, mi.rf_accuracy) + '%)';
            document.getElementById('model-details').innerHTML = `
                <div>Model đang dùng: <strong>${mi.best_model}</strong></div>
                <div>Dataset: <strong>${mi.dataset_size.toLocaleString()}</strong> mẫu</div>
                <div>Huấn luyện lúc: <strong>${mi.trained_at}</strong></div>
                <div>Thời gian train: <strong>${mi.training_duration_seconds}s</strong></div>
            `;
        } else {
            document.getElementById('kpi-accuracy').textContent = 'N/A';
            document.getElementById('model-badge').innerHTML = '<i class="fa-solid fa-exclamation-triangle me-1"></i> Chưa train';
        }

        document.getElementById('ds-size').textContent = data.dataset_info.size.toLocaleString();
        document.getElementById('ds-topics').textContent = data.dataset_info.topics || 4;
        document.getElementById('ds-emotions').textContent = data.dataset_info.emotions || '-';

        renderChart('chartTopic', data.topic_chart, topicChart, c => topicChart = c);
        renderChart('chartEmotion', data.emotion_chart, emotionChart, c => emotionChart = c);

        const histEl = document.getElementById('training-history');
        if (data.training_history.length) {
            histEl.innerHTML = data.training_history.map(h => `
                <div class="log-row py-2 d-flex justify-content-between">
                    <span><strong>${h.model_name}</strong> thắng (SVM: ${h.svm_accuracy}%, RF: ${h.rf_accuracy}%)</span>
                    <span class="text-muted">${new Date(h.created_at).toLocaleString('vi-VN')}</span>
                </div>
            `).join('');
        } else {
            histEl.innerHTML = '<p class="text-muted">Chưa có lịch sử huấn luyện trong DB.</p>';
        }

        const logsEl = document.getElementById('logs-table');
        if (data.recent_logs.length) {
            logsEl.innerHTML = data.recent_logs.map(l => `
                <tr>
                    <td>${new Date(l.created_at).toLocaleString('vi-VN')}</td>
                    <td>${l.full_name || 'N/A'}</td>
                    <td class="text-truncate" style="max-width:150px">${escapeHtml(l.post_content || '')}</td>
                    <td>${escapeHtml(l.predicted_topic || '')}</td>
                    <td>${escapeHtml(l.predicted_emotion || '')}</td>
                    <td>${l.processing_time_ms ? (l.processing_time_ms/1000).toFixed(1)+'s' : '-'}</td>
                    <td><span class="badge bg-${l.status==='success'?'success':(l.status==='fallback'?'warning':'danger')}">${l.status}</span></td>
                </tr>
            `).join('');
        } else {
            logsEl.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Chưa có log AI. Hãy thử nút "AI Vẽ" trên bảng tin.</td></tr>';
        }

        const imgEl = document.getElementById('recent-images');
        if (data.recent_ai_images.length) {
            imgEl.innerHTML = data.recent_ai_images.map(p => `
                <div class="col-md-3 col-6">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <img src="${escapeHtml(p.generated_image_url)}" class="w-100" style="height:120px;object-fit:cover" alt="">
                        <div class="p-2 small text-truncate">${escapeHtml(p.content)}</div>
                    </div>
                </div>
            `).join('');
        } else {
            imgEl.innerHTML = '<div class="col-12 text-muted small">Chưa có ảnh AI nào.</div>';
        }

        if (data.has_confusion_matrix) {
            document.getElementById('cm-image').src = 'confusion_matrix.png?t=' + Date.now();
        }
    } catch (e) { console.error(e); }
}

function renderChart(canvasId, chartData, existing, setter) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    if (existing) existing.destroy();
    const colors = ['#6366f1','#8b5cf6','#d946ef','#0ea5e9','#10b981','#f59e0b','#ef4444','#64748b'];
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.map(d => d.label),
            datasets: [{ data: chartData.map(d => d.count), backgroundColor: colors }]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
    });
    setter(chart);
}

function escapeHtml(t) {
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

loadStats();

<?php if ($is_user_admin): ?>
document.getElementById('btn-retrain')?.addEventListener('click', async function() {
    if (!confirm('Huấn luyện lại model? Quá trình có thể mất 30-60 giây.')) return;
    this.disabled = true;
    document.getElementById('train-progress').classList.remove('d-none');
    try {
        const res = await fetch('api_ai_train.php', { method: 'POST' });
        const data = await res.json();
        if (data.status === 'success') {
            alert('Huấn luyện thành công! Model: ' + data.data.best_model);
            loadStats();
        } else {
            alert('Lỗi: ' + (data.message || 'Không xác định'));
        }
    } catch (e) { alert('Lỗi mạng'); }
    finally {
        this.disabled = false;
        document.getElementById('train-progress').classList.add('d-none');
    }
});
<?php endif; ?>

document.getElementById('btn-test-classify').addEventListener('click', () => runTest('classify_only'));
document.getElementById('btn-test-full').addEventListener('click', () => runTest('full'));

async function runTest(mode) {
    const content = document.getElementById('test-content').value.trim();
    if (!content) return alert('Nhập nội dung test');
    const provider = document.getElementById('test-provider').value;
    const btn = mode === 'full' ? document.getElementById('btn-test-full') : document.getElementById('btn-test-classify');
    btn.disabled = true;
    try {
        const res = await fetch('api_ai_test.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content, mode: mode === 'full' ? 'full' : 'classify_only', provider })
        });
        const data = await res.json();
        if (data.status !== 'success') return alert(data.message || 'Lỗi');
        const r = data.data;
        document.getElementById('test-result').classList.remove('d-none');
        document.getElementById('test-topic').textContent = 'Chủ đề: ' + (r.predicted_topic || r.topic);
        document.getElementById('test-emotion').textContent = 'Cảm xúc: ' + (r.predicted_emotion || r.emotion);
        document.getElementById('test-time').textContent = r.processing_time_ms ? (r.processing_time_ms/1000).toFixed(1)+'s' : '';
        const img = document.getElementById('test-image');
        if (r.image_url && mode === 'full') {
            img.src = r.image_url;
            img.classList.remove('d-none');
        } else {
            img.classList.add('d-none');
        }
        loadStats();
    } catch (e) { alert('Lỗi mạng'); }
    finally { btn.disabled = false; }
}
</script>
</body>
</html>
