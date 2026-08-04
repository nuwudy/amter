# Video Hosting & Optimization Strategy

This guide provides a technical assessment of whether to host video clips directly on your CyberPanel server, how to eliminate bandwidth surge costs, and precise methods to optimize video sizes (1–5 MB) while maintaining crisp visual quality.

---

## 📊 Part 1: Is Direct Hosting Advisable?

**Yes, absolutely.** Given your current metrics and the nature of your files, hosting them directly is highly practical if configured correctly.

### The Assessment

| Metric | Current Status | Assessment |
| :--- | :--- | :--- |
| **Storage Space** | **71 GB Free** of 95 GB | **Excellent.** Your 3 GB video library will consume only ~4% of your remaining space. You have plenty of headroom. |
| **File Sizes** | **1 MB – 5 MB** per clip | **Very Light.** These are not large 500MB movies. Serving them is lightweight and puts very little stress on the server’s disk IO. |
| **CPU / RAM** | 2 Cores (57%), 8GB RAM (27%) | **Healthy.** Serving static files uses almost zero RAM and minimal CPU. |

### ⚠️ The Only Risk: Bandwidth & Concurrency
The primary risk of direct hosting is **concurrent traffic bursts**. If 50 students load a 5 MB video clip at the exact same moment, it requires a burst of 250 MB of data egress. Depending on your Hostinger VPS port speed (usually 100Mbps or 1Gbps), this can cause momentary buffering.

### 🚀 The Solution: Cloudflare's Free Tier
By pointing `amter.in` to Cloudflare, all video traffic is intercepted and cached at the network edge, reducing VPS bandwidth to zero. **See the detailed setup below.**

---

## ⚡ How to Eliminate Surge Bandwidth with Cloudflare (Free Tier)

Follow these step-by-step instructions to put your website `amter.in` behind Cloudflare and offload 100% of your video traffic.

### Step 1: Connect your domain to Cloudflare
1. Create a free account at **[cloudflare.com](https://www.cloudflare.com/)**.
2. Click **Add a site**, enter `amter.in`, and select the **Free Plan** ($0).
3. Cloudflare will scan your existing DNS records automatically. Verify that it found the `A` record pointing to your Hostinger VPS IP address.

### Step 2: Update Nameservers at Hostinger
Cloudflare will provide you with two unique Nameservers (e.g., `lorna.ns.cloudflare.com` and `todd.ns.cloudflare.com`).
1. Log into your **Hostinger Account** (or wherever you purchased `amter.in`).
2. Go to **Domains** > **amter.in** > **Nameservers**.
3. Click **Change Nameservers**, delete the Hostinger ones, paste Cloudflare’s two nameservers, and save.
*Note: DNS propagation usually takes 5 to 30 minutes.*

### Step 3: Verify DNS Proxy (The Orange Cloud)
1. Go to the **DNS** tab in your Cloudflare dashboard.
2. Find the `A` record for `amter.in` (and `www`).
3. Make sure the toggle under "Proxy Status" is set to **Proxied** (the Cloud icon will turn Orange). This hides your VPS IP and activates the CDN layer.

### Step 4: Configure SSL
Since CyberPanel is already issuing Let’s Encrypt SSL certificates on your VPS origin:
1. In Cloudflare, navigate to **SSL/TLS** > **Overview**.
2. Set the encryption mode to **Full (Strict)**. This ensures traffic is 100% secure between the browser, Cloudflare, and your server.

### Step 5: Set the "Cache Everything" Media Rule (Critical for Videos)
By default, Cloudflare caches files intelligently, but to *guarantee* the videos never hit your origin server again, create a Cache Rule:
1. In Cloudflare, go to **Caching** > **Cache Rules** > **Create Rule**.
2. Name the rule: `Cache Course Videos`.
3. Under **Field**, select `URI Path`.
4. Under **Operator**, select `contains`.
5. Under **Value**, type `/storage/` (since Laravel stores public files here).
6. Under **Cache Eligibility**, select **Eligible for cache** (forces caching).
7. Under **Edge TTL**, choose **Override origin** and set the duration to **1 Month** (instructs Cloudflare to keep the video in their cache for 30 days without bothering your server).
8. Under **Browser TTL**, choose **Override origin** and set to **1 Month** (saves bandwidth for students watching the same video twice).
9. Click **Deploy**.

---

## 🎥 Part 3: Video Optimization Blueprint

To ensure your videos look premium while staying under 1–3 MB, you need to strip unnecessary data (like ultra-high bitrates and 60 FPS frames) that the human eye won't notice on a web/mobile screen.

### Ideal Settings for Courseware (10-30s Clips)
*   **Codec:** `H.264 (AVC)` (Highest compatibility across all iOS, Android, and desktop browsers).
*   **Resolution:** `720p` (1280x720). Perfect for mobile/web players, saves ~50% size vs 1080p.
*   **Frame Rate:** `24 FPS` or `30 FPS` (Constant Frame Rate). Never use 60 FPS.
*   **Audio:** `AAC-LC`, **Mono**, `64kbps` to `96kbps`. Cutting out stereo data saves 50% of audio size.

---

## 🛠️ How to Optimize (Step-by-Step)

### Option A: The Visual Way (Highly Recommended)
Download **[Handbrake](https://handbrake.fr/)** (Free, Open-Source) and use these exact settings:

1.  **Summary Tab:**
    *   Format: `MP4`
    *   Check the box for **Web Optimized** (Moves metadata to the front so the video plays instantly before downloading).
2.  **Dimensions Tab:**
    *   Resolution Limit: `720p HD` (1280x720)
3.  **Video Tab:**
    *   Video Encoder: `H.264 (x264)`
    *   Framerate: `Peak Framerate` -> `30` (or `Same as source`)
    *   **Constant Quality (RF):** Set to `23` or `24`.
        *   `23-24` is the "invisible compression" sweet spot.
    *   Encoder Preset: `Slow` (Produces smaller files for same quality).
4.  **Audio Tab:**
    *   Codec: `AAC`
    *   Mixdown: `Mono`
    *   Bitrate: `80` or `96`
5.  **Batch Process:** Drag an entire folder of videos into Handbrake and click **Start Queue** to bulk-convert.

---

### Option B: The Fast Way via Command Line (FFmpeg)
If you prefer using a script to bulk-optimize, you can use **FFmpeg**. 

Optimal compression command:
```powershell
ffmpeg -i input.mp4 -vcodec libx264 -crf 24 -preset slow -vf "scale=-2:720" -movflags +faststart -acodec aac -b:a 96k -ac 1 output.mp4
```

---

## 📋 Checklist for Uploading to Amter
When uploading to your Laravel admin portal:
1.  [ ] Optimize locally using Handbrake/FFmpeg.
2.  [ ] Check the size (Ensure it's under 2.5 MB).
3.  [ ] Ensure the file has `.mp4` extension.
4.  [ ] Upload via the **Local Video** block in your admin dashboard.
