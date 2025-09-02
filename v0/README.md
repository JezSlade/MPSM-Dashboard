# MPSM Dashboard - Production-Ready Dark SPA

A sophisticated Single Page Application built with vanilla JavaScript, featuring a dark glassmorphic theme and comprehensive MPS Monitor API simulation.

## 🚀 Quick Start

1. **Deploy to Static Host**
   \`\`\`bash
   # Upload all files to your static hosting service
   # No build step required - runs directly in browser
   \`\`\`

2. **Local Development**
   \`\`\`bash
   # Serve files with any static server
   npx http-server public/mpsm -p 8080
   # or
   python -m http.server 8080
   \`\`\`

3. **Access Dashboard**
   \`\`\`
   http://localhost:8080/index.html
   \`\`\`

## 📁 Architecture

\`\`\`
/public/mpsm/
├── index.html              # Entry point with dark glass UI
├── main.js                 # App bootstrap
├── config/                 # Configuration files
│   ├── app-config.js       # App settings
│   ├── theme-config.js     # Dark glass theme
│   └── constants.js        # Application constants
├── api/                    # Mock API engine
│   ├── mock-fetch.js       # Fetch override
│   ├── api-router.js       # Request routing
│   ├── handlers/           # API endpoint handlers
│   └── data/               # Mock data (Swagger-aligned)
├── ui/                     # UI components
│   ├── dashboard-ui.js     # Main dashboard renderer
│   └── components/         # Reusable glass components
├── controllers/            # Business logic
│   ├── app-controller.js   # Main app coordinator
│   ├── dashboard-controller.js
│   ├── device-controller.js
│   ├── alert-controller.js
│   └── customer-controller.js
└── utils/                  # Utility functions
    ├── data-processor.js   # Data formatting
    ├── event-manager.js    # Event handling
    └── storage-manager.js  # Local storage
\`\`\`

## 🎨 Features

- **Dark Glassmorphic UI** - Modern backdrop-filter aesthetic
- **Responsive Grid Layout** - Adapts to all screen sizes
- **Real-time Updates** - Auto-refresh with connection handling
- **Mock API Engine** - 100% Swagger-aligned simulation
- **Modular Architecture** - Clean separation of concerns
- **Zero Dependencies** - Pure vanilla JavaScript
- **Offline Ready** - Works without network connection

## 🔧 Customization

### Theme Colors
Edit `config/theme-config.js` to customize the color palette:
\`\`\`javascript
colors: {
  "accent-blue": "#3b82f6",     // Primary accent
  "bg-glass": "rgba(30, 30, 30, 0.8)",  // Glass background
  // ... more colors
}
\`\`\`

### API Endpoints
Extend `api/handlers/` to add new endpoints:
\`\`\`javascript
// api/handlers/new-handler.js
export class NewHandler {
  supportedMethods = ["GET", "POST"]
  
  async getData(request, endpoint) {
    // Your implementation
  }
}
\`\`\`

### Dashboard Widgets
Add new widgets in `ui/dashboard-ui.js`:
\`\`\`javascript
createWidgetCard(widget) {
  switch (widget.type) {
    case "your-widget":
      return this.createYourWidget(widget)
    // ... existing cases
  }
}
\`\`\`

## 📊 Mock Data

The dashboard simulates realistic MPS Monitor data:
- **Customers**: 3 sample customers with different tiers
- **Devices**: Printers, scanners, copiers with status
- **Alerts**: Critical, warning, and info alerts
- **Metrics**: System health and performance data

## 🌐 Deployment

### Static Hosting (Recommended)
- **Vercel**: Drag & drop the `/public/mpsm/` folder
- **Netlify**: Upload folder or connect to Git
- **GitHub Pages**: Push to repository and enable Pages

### Server Requirements
- **None** - Pure client-side application
- **HTTPS** - Required for backdrop-filter support
- **Modern Browser** - ES6 modules and CSS backdrop-filter

## 🔍 Browser Support

- Chrome 76+ (full support)
- Firefox 103+ (full support)  
- Safari 14+ (full support)
- Edge 79+ (full support)

## 📱 Mobile Support

Fully responsive with touch-optimized interactions:
- Adaptive grid layouts
- Touch-friendly hover states
- Mobile-first CSS approach
- Optimized for tablets and phones

---

**Ready to deploy!** This dashboard requires no build process and works immediately when uploaded to any static hosting service.
