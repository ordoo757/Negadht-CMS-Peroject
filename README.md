# 🧠 NeuroCMS

سیستم مدیریت محتوای هوشمند و ماژولار

## ویژگی‌ها

- ✅ هسته هوش مصنوعی با قابلیت یادگیری
- ✅ معماری کاملاً ماژولار و کامپوننت‌محور
- ✅ چندزبانه (فارسی، عربی، انگلیسی)
- ✅ امنیت پیشرفته با مانیتورینگ لحظه‌ای
- ✅ سازنده منو، فرم، گزارش و جدول هوشمند
- ✅ مدیریت قالب با قابلیت ساخت AI
- ✅ نصب‌کننده حرفه‌ای
- ✅ خروجی ZIP برای ماژول‌ها و کامپوننت‌ها

## نیازمندی‌ها

- PHP >= 8.3
- MySQL >= 8.0 یا MariaDB >= 10.6
- Extensions: pdo, pdo_mysql, mbstring, openssl, json, tokenizer, xml, ctype, fileinfo, bcmath, zip

## نصب

1. Clone repository:
```bash
git clone https://github.com/your-org/neurocms.git
cd neurocms
```

2. Install dependencies:
```bash
composer install
```

3. Set permissions:
```bash
chmod -R 755 storage bootstrap/cache public/uploads
```

4. Go to `/install` in your browser and follow the wizard

## ساختار پروژه

```
neurocms/
├── app/
│   ├── Core/              # هسته مرکزی
│   │   ├── Foundation/    # کلاس‌های پایه
│   │   ├── Providers/     # Service Providers
│   │   ├── Middleware/    # Middlewareها
│   │   └── Helpers/       # توابع کمکی
│   ├── Modules/           # ماژول‌ها و کامپوننت‌ها
│   │   ├── AiKernel/      # هوش مصنوعی
│   │   ├── MenuMaker/     # سازنده منو
│   │   ├── TemplateManager/ # مدیریت قالب
│   │   ├── FormCreator/   # سازنده فرم
│   │   ├── ReportGenerator/ # گزارش‌ساز
│   │   ├── TableGenerator/  # سازنده جدول
│   │   ├── User/          # مدیریت کاربر
│   │   ├── LanguageManager/ # مدیریت زبان
│   │   ├── ModuleMaker/   # سازنده ماژول
│   │   ├── ComponentMaker/ # سازنده کامپوننت
│   │   └── PluginMaker/   # سازنده پلاگین
│   └── Installer/         # سیستم نصب
├── config/
├── database/
│   └── migrations/        # Migrationها
├── public/
│   └── templates/         # قالب‌های سایت و مدیریت
├── resources/
│   └── lang/              # فایل‌های زبان
├── routes/
└── storage/
```

## API Keys

برای استفاده از قابلیت‌های AI، کلیدهای API زیر را در `.env` تنظیم کنید:

```env
OPENAI_API_KEY=your_openai_key
CLAUDE_API_KEY=your_claude_key
```

## مجوز

MIT License
# Negadht-CMS-Peroject
