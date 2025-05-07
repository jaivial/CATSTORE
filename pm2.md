root@srv736989:~# pm2 list
┌────┬────────────────────────────────────┬─────────────┬─────────┬─────────┬──────────┬────────┬──────┬───────────┬──────────┬──────────┬──────────┬──────────┐
│ id │ name                               │ namespace   │ version │ mode    │ pid      │ uptime │ ↺    │ status    │ cpu      │ mem      │ user     │ watching │
├────┼────────────────────────────────────┼─────────────┼─────────┼─────────┼──────────┼────────┼──────┼───────────┼──────────┼──────────┼──────────┼──────────┤
│ 8  │ astro-server                       │ default     │ N/A     │ fork    │ 546050   │ 6D     │ 2    │ online    │ 0%       │ 859.5mb  │ root     │ disabled │
│ 3  │ email-server                       │ default     │ 0.0.1   │ fork    │ 546030   │ 6D     │ 4    │ online    │ 0%       │ 55.9mb   │ root     │ disabled │
│ 7  │ guillermofernandez-email-server    │ default     │ 1.0.0   │ fork    │ 546041   │ 6D     │ 2    │ online    │ 0%       │ 61.1mb   │ root     │ disabled │
│ 1  │ marcosgoweb                        │ default     │ N/A     │ fork    │ 546016   │ 6D     │ 4    │ online    │ 0%       │ 10.0mb   │ root     │ disabled │
│ 10 │ server                             │ default     │ 0.0.1   │ fork    │ 554697   │ 5D     │ 54   │ online    │ 0%       │ 55.1mb   │ root     │ enabled  │
│ 11 │ todolist-app                       │ default     │ N/A     │ fork    │ 625433   │ 2h     │ 0    │ online    │ 0%       │ 58.1mb   │ root     │ disabled │
│ 0  │ tourtovalencia                     │ default     │ N/A     │ fork    │ 546022   │ 6D     │ 4    │ online    │ 0%       │ 62.7mb   │ root     │ disabled │
└────┴────────────────────────────────────┴────