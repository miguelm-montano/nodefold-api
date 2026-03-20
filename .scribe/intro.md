# Introduction

RESTful API for managing design assets organized in folders. All protected endpoints require a Bearer token obtained via POST /api/v1/login.

## Quick start

1. **Register or login** via `POST /api/v1/register` or `POST /api/v1/login` to obtain your Bearer token.
2. **Create a folder** via `POST /api/v1/folders` to organize your resources.
3. **Add resources** via `POST /api/v1/folders/{id}/resources` — supported types are `image`, `font`, `color_palette`, `icon` and `web`.
4. **Tag your resources** by including a `tags` field when creating or updating a resource. Tags are created automatically and deleted when they become orphaned.
5. **Filter and search** your resources via `GET /api/v1/resources` using query params like `?search=`, `?tag=` or `?tagged=true`.

## Roles

- `user` — can manage their own folders, resources and tags.
- `admin` — has additional access to user management and platform statistics via `/api/v1/admin/*` endpoints.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>



