<div class="welcome-step">
    <div class="text-center mb-4">
        <i class="fas fa-cube fa-4x text-primary mb-3"></i>
        <h3><?php echo $lang->get('welcome_text'); ?></h3>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="feature-box">
                <i class="fas fa-brain text-primary"></i>
                <h5><?php echo $lang->get('feature_ai'); ?></h5>
                <p>هسته هوش مصنوعی با قابلیت یادگیری و پردازش زبان طبیعی</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="feature-box">
                <i class="fas fa-puzzle-piece text-success"></i>
                <h5><?php echo $lang->get('feature_modular'); ?></h5>
                <p>معماری کاملاً ماژولار با قابلیت توسعه و شخصی‌سازی</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="feature-box">
                <i class="fas fa-globe text-info"></i>
                <h5><?php echo $lang->get('feature_multilingual'); ?></h5>
                <p>پشتیبانی از ۲۱ زبان زنده دنیا با قابلیت راست‌چین و چپ‌چین</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="feature-box">
                <i class="fas fa-shield-alt text-danger"></i>
                <h5><?php echo $lang->get('feature_security'); ?></h5>
                <p>امنیت پیشرفته با فایروال، آنتی‌ویروس و محافظت در برابر حملات</p>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <i class="fas fa-info-circle"></i>
        <strong>نکته:</strong>
        نصب NeuroCMS شامل مراحل زیر است:
        <ol class="mb-0 mt-2">
            <li>پذیرش مجوز GNU GPL v3.0</li>
            <li>بررسی نیازمندی‌های سیستم</li>
            <li>تنظیمات دیتابیس</li>
            <li>تنظیمات سایت</li>
            <li>ایجاد حساب کاربری مدیر</li>
            <li>انتخاب ماژول‌های پیش‌فرض</li>
            <li>نهایی‌سازی نصب</li>
        </ol>
    </div>

    <form id="installer-form" method="POST" action="?step=2">
        <input type="hidden" name="step" value="1">
        <input type="hidden" name="action" value="proceed">
    </form>
</div>

<style>
.feature-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}
.feature-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.feature-box i {
    font-size: 28px;
    margin-bottom: 10px;
    display: block;
}
.feature-box h5 {
    font-weight: 600;
    margin-bottom: 5px;
}
.feature-box p {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 0;
}
</style>
