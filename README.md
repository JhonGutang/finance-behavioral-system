# Spendlytic: Finance Behavioral System

An engineering-focused finance tracker with adaptive feedback engine.

## Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd finance-behavioral-system
   ```

2. **Start Backend**
   ```bash
   cd app/backend
   php artisan serve
   ```
   Backend runs on http://localhost:8000

3. **Start Frontend** (in a new terminal)
   ```bash
   cd app/web
   npm run dev
   ```
   Frontend runs on http://localhost:5173

4. **Open Browser**
   Navigate to http://localhost:5173

   You should see a success message from the backend confirming the connection works!

## Documentation

- **[AGENTS.MD](./AGENTS.MD)** - Development guidelines and project overview
- **[SETUP.md](./SETUP.md)** - Detailed setup and verification instructions
- **[docs/low-concept.md](./docs/low-concept.md)** - MVP overview and scope
- **[docs/finance-tracking.md](./docs/finance-tracking.md)** - System design and architecture

## Project Structure

```
finance-behavioral-system/
├── app/
│   ├── backend/     # Laravel API
│   └── web/         # Vue 3 + TypeScript frontend
├── docs/            # System documentation
└── AGENTS.MD        # Development guidelines
```

## Tech Stack

**Frontend**: Vue 3, TypeScript, Vite, Axios  
**Backend**: Laravel, MySQL  
**Testing**: Jest (Frontend), Pest (Backend)

## Current Status

✅ Backend and frontend environment set up  
✅ CORS configured  
✅ API communication verified  
✅ Transaction tracking  
✅ Pattern detection rules  
✅ Feedback engine

## License

MIT
