# تشغيل تجربة شبيهة بـ Lovable محلياً في VS Code

> ملخص: **مفيش طريقة رسمية** تشغّل "موديلات Lovable" على جهازك، لأن Lovable نفسه عميل عند Anthropic / Google / OpenAI، والـ agent loop والـ system prompts ملكية خاصة. لكن فيه بدائل قوية تديك تجربة قريبة جداً.

---

## 1) ليه مينفعش "موديل Lovable" محلياً؟

| السبب | التفصيل |
|---|---|
| مفيش API عام | Lovable مش بيوفّر endpoint تستدعي بيه نفس الموديل |
| موديلات مغلقة | Claude / Gemini / GPT — كلها Cloud APIs مدفوعة |
| الـ Agent خاص | الـ tools, system prompt, file editing logic — كلها داخلية |
| البيئة Sandboxed | الـ preview بيشتغل على Cloudflare Workers مش على جهازك |

---

## 2) أفضل البدائل (مرتّبة بالأقرب لتجربة Lovable)

### 🥇 Cline (الأقرب فعلاً)
إضافة VS Code مفتوحة المصدر، بتشتغل كـ AI agent كامل (يقرأ/يكتب ملفات، يشغّل terminal).
```bash
# في VS Code:
# Extensions → ابحث عن "Cline" → Install
# ثم أضف Anthropic API Key أو OpenRouter
```
- **المزايا:** Agent loop شبيه بـ Lovable، بيستخدم Claude Sonnet نفسه.
- **التكلفة:** Pay-as-you-go على Anthropic / OpenRouter.

### 🥈 Cursor
IDE كامل (fork من VS Code) فيه AI مدمج.
- Download: https://cursor.com
- Composer mode = أقرب حاجة لتجربة Lovable.

### 🥉 GitHub Copilot Workspace / Copilot Agent
```bash
# Extensions → "GitHub Copilot" + "GitHub Copilot Chat"
```
- يحتاج اشتراك Copilot ($10/شهر).

### 🆓 موديلات محلية مجانية (Ollama + Continue)
```bash
# 1) ثبّت Ollama
curl -fsSL https://ollama.com/install.sh | sh   # Linux/Mac
# Windows: نزّل من https://ollama.com/download

# 2) شغّل موديل كودينج
ollama pull qwen2.5-coder:7b
# أو الأكبر:
ollama pull qwen2.5-coder:32b
ollama pull deepseek-coder-v2

# 3) في VS Code:
# Extensions → "Continue" → Install
# Settings → Add Model → Provider: Ollama → Model: qwen2.5-coder
```
- **مجاني 100%** وأوفلاين.
- الجودة أقل من Claude لكن كويسة للـ autocomplete والـ refactor.

---

## 3) جدول مقارنة سريع

| الأداة | التكلفة | Agent Loop | جودة | الأقرب لـ Lovable |
|---|---|---|---|---|
| **Cline** | API usage | ✅ كامل | ⭐⭐⭐⭐⭐ | 🥇 |
| **Cursor** | $20/شهر | ✅ | ⭐⭐⭐⭐⭐ | 🥈 |
| **Copilot** | $10/شهر | ⚠️ محدود | ⭐⭐⭐⭐ | 🥉 |
| **Continue + Ollama** | مجاني | ⚠️ chat فقط | ⭐⭐⭐ | بديل اقتصادي |

---

## 4) للاشتغال على مشروعك الـ Laravel ده تحديداً

أنصحك بـ **Cline + Claude Sonnet**:

```bash
# 1) ارفع المشروع على GitHub (لو مش مرفوع)
cd "PROJECT - UNILAB/uni-lab"
git init && git add . && git commit -m "init"
git remote add origin <your-repo>
git push -u origin main

# 2) Clone محلياً
git clone <your-repo>
cd uni-lab

# 3) ثبّت Laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build

# 4) شغّل
php artisan serve
# في تيرمنال تاني:
php artisan queue:work
```

ثم افتح المجلد في VS Code وفعّل Cline.

---

## 5) لو حابب أساعدك تجهّز

- 🐳 `docker-compose.yml` كامل (PHP + MySQL + Nginx + phpMyAdmin)
- ⚙️ سكربت bash يثبّت كل حاجة بأمر واحد
- 📦 ملف `.cursorrules` أو `.clinerules` فيه قواعد المشروع عشان الـ AI يفهم الكود بسرعة

قولي تحب أنهي فيهم.
