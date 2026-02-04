# Bird's-Eye View: Monorepo & PHP Application

## What We're Building

A **monorepo** containing a **plain PHP application** (no frameworks) that:

- **Renders client views** — HTML pages with optional templating
- **Maintains authorization** — login, sessions, protected routes
- **Performs database operations** — MySQL via PDO, structured access layer
- **Handles HTTP** — routing, request/response, clean entry point

You will describe the actual business functionality later; this document focuses on structure and capabilities.

---

## Monorepo Layout (High Level)

```
fly/
├── app/                    # PHP application
│   ├── public/             # Web root (index.php, assets)
│   ├── src/                # Application code
│   │   ├── Auth/            # Auth logic, session, login/logout
│   │   ├── Db/              # DB connection, queries, repositories
│   │   ├── Http/            # Request, Response, Router
│   │   └── View/            # View helper + templates/ (login, dashboard, layout)
│   ├── config/              # config.php, env.php (env-based)
│   ├── migrations/         # DB migrations
│   └── routes.php          # Route definitions
├── docs/                    # Documentation, schema
├── scripts/                 # migrate.php, seed.php, docker-*.sh
└── composer.json           # PHP dependencies (minimal: no framework)
```

Other top-level pieces (e.g. `tests/`, `docker/`, `.env.example`) can be added as we go.

---

## Request Flow (Bird's Eye)

1. **Entry point**  
   All HTTP requests hit `app/public/index.php`. No framework front controller; we build a tiny dispatcher ourselves.

2. **Bootstrap**  
   Load config (e.g. from env), create DB connection (PDO), optionally start session. No global state beyond what’s needed for request lifecycle.

3. **Routing**  
   A small router (in `Http/`) maps URL + method to a handler (closure or class). Routes: `/` → redirect to `/login` or `/dashboard`; `/login` (GET/POST); `/dashboard` (protected); `/logout` (POST, protected).

4. **Authorization**  
   Before running the handler, we check if the route is protected. If protected and user is not logged in → redirect to login or 401. Session holds user identity (e.g. user id).

5. **Handler**  
   Handler receives request (and optionally DB/auth services). It:
   - Talks to **Db** layer (repositories or simple query helpers) for MySQL
   - Builds data for the page
   - Returns a **Response** (e.g. HTML from a view, or redirect)

6. **Views**  
   Views are PHP templates (or a thin wrapper around them). Handler passes data; view echoes HTML. No full-blown framework templating; we can use plain PHP or a single small library if you prefer.

7. **Response**  
   We send status, headers, and body. No framework response object required; we can use a minimal `Response` abstraction for clarity.

---

## Main Capabilities (Summary)

| Concern            | Approach |
|--------------------|----------|
| **HTTP**           | Single entry point (`public/index.php`), small Router + Request/Response in `src/Http/` |
| **Database**       | PDO + MySQL; `Db/` for connection and query/repository layer |
| **Views**          | PHP templates under e.g. `src/View/templates/`, rendered by a simple View helper |
| **Authorization**  | Session-based login; Auth layer for login/logout and “is logged in?” / “current user” |
| **Config**         | Environment variables; optional `config/` files that read from env |

---

## What We're *Not* Using

- No PHP frameworks (no Laravel, Symfony, etc.)
- No heavy ORMs (we use PDO; we can add a thin repository/query layer)
- No frontend framework requirement in this doc (we can add JS later if needed)

---

## Next Steps (When You're Ready)

1. You describe the **functionality** (features, pages, roles, main entities).
2. We refine folder structure and add **concrete packages** in `composer.json` if any (e.g. env loader, optional small libs).
3. We implement **routing**, **bootstrap**, **DB layer**, **Auth**, and **views** step by step in the monorepo.

This gives us a clear bird’s-eye view: a framework-free PHP app in a monorepo that can render views, handle auth, and perform MySQL operations, ready for you to define the actual functionality next.
