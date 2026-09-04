@extends('admin.default.index')

@section('page-title', 'دستیار هوشمند')
@section('page-desc', 'دستیار AI پیشرفته برای مدیریت و تحلیل سیستم')

@section('breadcrumb')
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>هوش مصنوعی</span>
    <span class="separator"><i class="fas fa-chevron-left"></i></span>
    <span>دستیار هوشمند</span>
@endsection

@section('content')
<div class="grid-2">
    <!-- AI Chat Interface -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <h3><i class="fas fa-robot"></i> دستیار NeuroAI</h3>
            <div style="display: flex; gap: 0.5rem;">
                <span class="badge badge-success"><i class="fas fa-circle" style="font-size: 6px;"></i> آنلاین</span>
                <button class="btn btn-ghost btn-sm" onclick="clearChat()">
                    <i class="fas fa-trash"></i> پاک کردن
                </button>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="ai-chat-container" id="aiChatContainer" style="height: 500px; overflow-y: auto; padding: 1.5rem;">
                <div class="ai-message ai-bot">
                    <div class="ai-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="ai-content">
                        <p>سلام! من دستیار هوشمند NeuroCMS هستم. چطور می‌توانم به شما کمک کنم؟</p>
                        <span class="ai-time">همین الان</span>
                    </div>
                </div>
                
                <div class="ai-suggestions">
                    <button class="ai-suggestion-btn" onclick="sendAiMessage('وضعیت سیستم را تحلیل کن')">
                        📊 تحلیل وضعیت سیستم
                    </button>
                    <button class="ai-suggestion-btn" onclick="sendAiMessage('گزارش امنیتی امروز را بده')">
                        🔒 گزارش امنیتی
                    </button>
                    <button class="ai-suggestion-btn" onclick="sendAiMessage('بهینه‌سازی عملکرد را پیشنهاد بده')">
                        ⚡ بهینه‌سازی عملکرد
                    </button>
                    <button class="ai-suggestion-btn" onclick="sendAiMessage('یک قالب جدید پیشنهاد بده')">
                        🎨 پیشنهاد قالب
                    </button>
                </div>
            </div>
            
            <div class="ai-input-area" style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; gap: 0.75rem;">
                <input type="text" id="aiInput" class="form-control" placeholder="پیام خود را بنویسید..." 
                       style="flex: 1;" onkeypress="if(event.key==='Enter') sendAiMessage()">
                <button class="btn btn-primary" onclick="sendAiMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- AI Tools -->
<div class="grid-3">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-magic"></i> تولید محتوا</h3>
        </div>
        <div class="card-body">
            <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.875rem;">
                با استفاده از AI محتوای خود را تولید کنید
            </p>
            <div class="form-group">
                <textarea class="form-control" rows="3" placeholder="موضوع محتوا را بنویسید..."></textarea>
            </div>
            <button class="btn btn-primary w-full">
                <i class="fas fa-magic"></i> تولید محتوا
            </button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-code"></i> تولید کد</h3>
        </div>
        <div class="card-body">
            <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.875rem;">
                کدهای PHP، JS و CSS تولید کنید
            </p>
            <div class="form-group">
                <textarea class="form-control" rows="3" placeholder="توضیح کد مورد نظر..."></textarea>
            </div>
            <button class="btn btn-primary w-full">
                <i class="fas fa-code"></i> تولید کد
            </button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-image"></i> تولید تصویر</h3>
        </div>
        <div class="card-body">
            <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.875rem;">
                تصاویر با AI تولید کنید
            </p>
            <div class="form-group">
                <textarea class="form-control" rows="3" placeholder="توضیح تصویر مورد نظر..."></textarea>
            </div>
            <button class="btn btn-primary w-full">
                <i class="fas fa-image"></i> تولید تصویر
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ai-chat-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ai-message {
    display: flex;
    gap: 0.875rem;
    max-width: 80%;
}

.ai-message.ai-bot {
    align-self: flex-start;
}

.ai-message.ai-user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.ai-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.ai-user .ai-avatar {
    background: var(--gray-500);
}

.ai-content {
    background: var(--gray-50);
    padding: 0.875rem 1.125rem;
    border-radius: 14px;
    border-bottom-right-radius: 4px;
    position: relative;
}

.ai-user .ai-content {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border-bottom-right-radius: 14px;
    border-bottom-left-radius: 4px;
}

.ai-content p {
    margin: 0;
    line-height: 1.6;
    font-size: 0.9rem;
}

.ai-time {
    font-size: 0.7rem;
    color: var(--gray-400);
    margin-top: 0.35rem;
    display: block;
}

.ai-user .ai-time {
    color: rgba(255,255,255,0.7);
}

.ai-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0 0.5rem;
}

.ai-suggestion-btn {
    padding: 0.5rem 1rem;
    background: var(--gray-100);
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 0.8rem;
    color: var(--text);
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.ai-suggestion-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.ai-typing {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.5rem 0;
}

.ai-typing span {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.ai-typing span:nth-child(2) { animation-delay: 0.2s; }
.ai-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-10px); opacity: 1; }
}
</style>
@endpush

@push('scripts')
<script>
function sendAiMessage(text = null) {
    const input = document.getElementById('aiInput');
    const message = text || input.value.trim();
    if (!message) return;
    
    const container = document.getElementById('aiChatContainer');
    
    // Add user message
    const userMsg = document.createElement('div');
    userMsg.className = 'ai-message ai-user';
    userMsg.innerHTML = `
        <div class="ai-avatar"><i class="fas fa-user"></i></div>
        <div class="ai-content">
            <p>${escapeHtml(message)}</p>
            <span class="ai-time">همین الان</span>
        </div>
    `;
    container.appendChild(userMsg);
    
    // Clear input and suggestions
    input.value = '';
    const suggestions = container.querySelector('.ai-suggestions');
    if (suggestions) suggestions.remove();
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
    
    // Show typing indicator
    const typing = document.createElement('div');
    typing.className = 'ai-message ai-bot';
    typing.id = 'aiTyping';
    typing.innerHTML = `
        <div class="ai-avatar"><i class="fas fa-robot"></i></div>
        <div class="ai-content">
            <div class="ai-typing">
                <span></span><span></span><span></span>
            </div>
        </div>
    `;
    container.appendChild(typing);
    container.scrollTop = container.scrollHeight;
    
    // Simulate AI response
    setTimeout(() => {
        typing.remove();
        
        const responses = {
            'وضعیت سیستم را تحلیل کن': '✅ سیستم در وضعیت عالی قرار دارد.\n\n📊 آمار:\n• حافظه مصرفی: ۴۵٪\n• CPU: ۲۳٪\n• فضای دیسک: ۶۷٪\n\n💡 پیشنهاد: کش سیستم را پاک کنید تا عملکرد بهتر شود.',
            'گزارش امنیتی امروز را بده': '🔒 گزارش امنیتی ۲۴ ساعت گذشته:\n\n✅ ۱۲۷ ورود موفق\n⚠️ ۳ ورود ناموفق\n🚫 ۰ IP مسدود شده\n\n📈 روند: بهبود نسبت به دیروز (۵ ورود ناموفق)',
            'بهینه‌سازی عملکرد را پیشنهاد بده': '⚡ پیشنهادهای بهینه‌سازی:\n\n۱. فعال‌سازی کش Redis\n۲. فشرده‌سازی تصاویر\n۳. به‌روزرسانی به PHP 8.3\n۴. استفاده از CDN برای استاتیک‌ها\n\n🎯 انتظار: ۴۰٪ بهبود سرعت',
            'یک قالب جدید پیشنهاد بده': '🎨 پیشنهاد قالب:\n\n• نام: Modern Dashboard\n• رنگ: آبی-بنفش گرادیان\n• ویژگی‌ها:\n  - دارک مود\n  - رسپانسیو کامل\n  - انیمیشن‌های نرم\n  - پشتیبانی از RTL\n\nآیا مایل به ساخت هستید؟'
        };
        
        let response = responses[message] || 'متوجه شدم. در حال پردازش درخواست شما هستم...\n\n💡 می‌توانید از من درباره:\n• تحلیل سیستم\n• گزارش‌های امنیتی\n• بهینه‌سازی\n• ساخت محتوا\n\nسؤال بپرسید.';
        
        const botMsg = document.createElement('div');
        botMsg.className = 'ai-message ai-bot';
        botMsg.innerHTML = `
            <div class="ai-avatar"><i class="fas fa-robot"></i></div>
            <div class="ai-content">
                <p>${escapeHtml(response).replace(/\n/g, '<br>')}</p>
                <span class="ai-time">همین الان</span>
            </div>
        `;
        container.appendChild(botMsg);
        container.scrollTop = container.scrollHeight;
    }, 1500);
}

function clearChat() {
    const container = document.getElementById('aiChatContainer');
    container.innerHTML = `
        <div class="ai-message ai-bot">
            <div class="ai-avatar"><i class="fas fa-robot"></i></div>
            <div class="ai-content">
                <p>گفتگو پاک شد. چطور می‌توانم کمک کنم؟</p>
                <span class="ai-time">همین الان</span>
            </div>
        </div>
        <div class="ai-suggestions">
            <button class="ai-suggestion-btn" onclick="sendAiMessage('وضعیت سیستم را تحلیل کن')">📊 تحلیل وضعیت سیستم</button>
            <button class="ai-suggestion-btn" onclick="sendAiMessage('گزارش امنیتی امروز را بده')">🔒 گزارش امنیتی</button>
            <button class="ai-suggestion-btn" onclick="sendAiMessage('بهینه‌سازی عملکرد را پیشنهاد بده')">⚡ بهینه‌سازی عملکرد</button>
            <button class="ai-suggestion-btn" onclick="sendAiMessage('یک قالب جدید پیشنهاد بده')">🎨 پیشنهاد قالب</button>
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
