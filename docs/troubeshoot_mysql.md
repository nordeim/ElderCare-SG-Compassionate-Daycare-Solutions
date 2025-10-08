### MySQL startup failure on Ubuntu 24.04.1 with missing InnoDB redo logs

The logs point to an InnoDB crash during initialization: MySQL can’t find a redo log file in the redo directory, then fails to initialize the Data Dictionary and aborts. This usually indicates redo log corruption, an incomplete upgrade/restart, or filesystem/permission issues in the datadir or the dedicated redo directory.

---

## What’s failing and why it matters

- **Error insight:**  
  - **Missing redo log file:** `Missing redo log file ./#innodb_redo/#ib_redo17 (with start_lsn = 55678976)`  
  - **Cascade failure:** InnoDB plugin aborts ⇒ Data Dictionary fails ⇒ server aborts.  
- **Implications:**  
  - Redo logs are transactional recovery artifacts. If they’re incomplete or inaccessible, InnoDB can’t safely replay changes to bring tablespaces consistent.  
  - The data dictionary lives in InnoDB; if InnoDB doesn’t initialize, the server cannot start at all.

---

## Quick triage checks (low-risk)

1. **Confirm MySQL version and datadir paths**
   - **Command:**
     - `mysql --version`
     - `grep -E '^(datadir|innodb_redo_log|innodb_data_home_dir|innodb_log_group_home_dir)' /etc/mysql/mysql.conf.d/* /etc/mysql/my.cnf 2>/dev/null`
     - `ls -la /var/lib/mysql` (or your datadir)
     - `ls -la /var/lib/mysql/#innodb_redo` (or the configured redo dir)
   - **Goal:** Verify paths exist and match config; ensure the redo directory is correct.

2. **Permissions and ownership**
   - **Command:**
     - `sudo chown -R mysql:mysql /var/lib/mysql`
     - `sudo find /var/lib/mysql -maxdepth 1 -type d -name '#innodb_redo' -exec chown -R mysql:mysql {} \;`
     - `sudo chmod -R u+rwX,g+rX /var/lib/mysql`
   - **Goal:** MySQL (user `mysql`) must own datadir and redo directory; wrong perms often cause “missing” symptoms.

3. **Disk space and inode sanity**
   - **Command:**
     - `df -h /var /var/lib/mysql`
     - `df -i /var /var/lib/mysql`
   - **Goal:** Avoid silent failures from full disk or inode exhaustion.

4. **AppArmor/SELinux profile interference**
   - **Command:**
     - `sudo aa-status | grep mysql`
     - Check `/etc/apparmor.d/usr.sbin.mysqld` for allowed paths; if you moved datadir, update profile and reload: `sudo apparmor_parser -r /etc/apparmor.d/usr.sbin.mysqld`
   - **Goal:** Ensure MySQL can access non-default paths.

5. **File integrity of redo set**
   - **Command:**
     - `ls -l /var/lib/mysql/#innodb_redo | wc -l`  
     - `ls -l /var/lib/mysql/#innodb_redo/ | head -n 30`
   - **Goal:** Redo set should be contiguous files; missing members (e.g., `#ib_redo17`) breaks recovery.

---

## Safe recovery options (progressively more invasive)

### Option A — Let MySQL recreate redo logs (preferred, low data risk)
Use InnoDB’s ability to rebuild redo logs if the data files are consistent.

1. **Stop MySQL completely:**
   - `sudo systemctl stop mysql`

2. **Backup current redo directory:**
   - `sudo mkdir -p /var/lib/mysql/backup_redo_$(date +%F_%H%M)`
   - `sudo mv /var/lib/mysql/#innodb_redo/* /var/lib/mysql/backup_redo_$(date +%F_%H%M)/`

3. **Ensure redo dir exists and is owned by mysql:**
   - `sudo chown -R mysql:mysql /var/lib/mysql/#innodb_redo`
   - `sudo chmod 700 /var/lib/mysql/#innodb_redo`

4. **Start MySQL:**
   - `sudo systemctl start mysql`
   - Check: `journalctl -u mysql -n 200 --no-pager`

If data files are consistent, MySQL will recreate the redo log set and start. If it still errors out, proceed.

### Option B — Temporary forced recovery to dump data (moderate risk, read-only)
Enable InnoDB forced recovery to boot read-only, then dump databases.

1. **Add to `/etc/mysql/mysql.conf.d/mysqld.cnf`:**
   ```
   [mysqld]
   innodb_force_recovery = 1
   ```
   - If `1` fails, try incrementally `2` → `3` → up to `4`. Avoid `5/6` unless last resort; they risk further corruption.

2. **Restart:**
   - `sudo systemctl restart mysql`

3. **Dump your data:**
   - `mysqldump --all-databases --single-transaction --quick --routines --events > /var/backups/mysql_full_$(date +%F).sql`

4. **Remove forced recovery and attempt normal start:**
   - Remove `innodb_force_recovery`
   - `sudo systemctl restart mysql`

If normal start still fails, you have clean dumps to restore after rebuild.

### Option C — Rebuild system tablespace and data dictionary (high effort)
As a last resort when redo and DD are inconsistent.

1. **Full backup of datadir:**
   - `sudo systemctl stop mysql`
   - `sudo tar -C /var/lib -czf /var/backups/mysql_datadir_$(date +%F).tar.gz mysql`

2. **Move aside InnoDB core files:**
   - `sudo mv /var/lib/mysql/ibdata1 /var/lib/mysql/ibdata1.bak_$(date +%F_%H%M)`
   - `sudo mv /var/lib/mysql/ibtmp1 /var/lib/mysql/ibtmp1.bak_$(date +%F_%H%M)`
   - `sudo mv /var/lib/mysql/#innodb_redo /var/lib/mysql/#innodb_redo.bak_$(date +%F_%H%M)`

3. **Initialize fresh system tablespace:**
   - `sudo systemctl start mysql`  
   - This will recreate system tablespace and redo. You will likely need to restore user databases from dump or from clean tablespaces (if using file-per-table and those files are consistent, you may be able to reattach after export/import).

---

## Configuration sanity checklist

- **MySQL 8.0 redo settings:**  
  - MySQL 8.0 uses a redo log directory `#innodb_redo` in datadir. Ensure it matches the effective datadir and isn’t symlinked to a blocked path.
- **File-per-table:**  
  - Confirm `innodb_file_per_table=ON` for simpler recovery of individual `.ibd` files.
- **Crash-safe settings:**  
  - `innodb_flush_log_at_trx_commit=1` (true durability)  
  - `sync_binlog=1` if binary logging is enabled.
- **Clean startup flags:**  
  - After recovery, ensure `innodb_force_recovery` is removed.

---

## Diagnostics to capture before and after each change

- **Service logs:**  
  - `journalctl -xeu mysql.service --since "10 min ago"`
- **MySQL error log path:**  
  - Check `/etc/mysql/mysql.conf.d/mysqld.cnf` for `log_error`. Default is often `/var/log/mysql/error.log`.  
  - `sudo tail -n 200 /var/log/mysql/error.log`
- **Effective config:**  
  - `mysqld --verbose --help | sed -n '1,/Variables and options/p'` (prints compiled defaults)  
  - `my_print_defaults mysqld`
- **Filesystem health:**  
  - `sudo dmesg | tail -n 100` for I/O errors.

Document findings in a short runbook so we can track each change and the observed effect.

---

## Risk-managed recovery path (recommended)

1. **Backup first:** Tar the datadir and copy off-host.  
2. **Try Option A (recreate redo logs).**  
3. **If still failing, use Option B (forced recovery=1–3), dump all databases.**  
4. **Remove forced recovery; attempt normal start.**  
5. **If failure persists, rebuild system tablespace (Option C) and restore from dump.**  
6. **Post-recovery hardening:** verify perms, AppArmor, disk health, and crash-safety settings.

---

## Monitoring and tests post-fix

- **Health checks:**  
  - `mysqladmin ping -uroot -p` in a systemd watchdog script.  
- **Smoke tests:**  
  - Create table, insert, restart MySQL, verify persistence.  
- **Alerts:**  
  - Log error regex for InnoDB init failures; disk space alerts on `/var` and datadir.  
- **Backup verification:**  
  - Nightly `mysqldump` or logical backup; weekly restore test on a staging VM.

---

## Clarifying questions to tailor the fix

- What is your current datadir path, and did it change recently?  
- Did you upgrade MySQL or Ubuntu just before this started?  
- Do you have recent backups or logical dumps?  
- Are there non-default settings for `innodb_log_group_home_dir` or symlinks in the datadir?  
- Can you share `ls -l /var/lib/mysql/#innodb_redo` and the last 100 lines of `/var/log/mysql/error.log`?

If you confirm alignment, I can give you a precise, copy-paste runbook tailored to your paths and constraints, plus a minimal CI health-check script to prevent this from reoccurring.

---

### InnoDB redo set integrity and what your listing implies

Your redo directory shows only two files:
- **Present:** `#ib_redo16` and `#ib_redo18`
- **Missing:** `#ib_redo17` (the server error also references this exact missing file)

InnoDB’s redo logs form a contiguous ring. Startup recovery needs a complete, internally consistent sequence that matches the headers, LSN ranges, and the expected count. You can’t “roll back” to `#ib_redo16` or “discard” `#ib_redo18` to make recovery work; the missing `#ib_redo17` breaks the sequence, and selective deletion won’t make the state consistent. The viable choices are either restore the missing file from backup or force InnoDB to recreate the entire redo set.

---

## Direct answer

- **No, you cannot roll back to `#ib_redo16` nor fix this by deleting `#ib_redo18`.** InnoDB requires the full contiguous set; with `#ib_redo17` missing, recovery fails.
- **Safest path:** Back up and let MySQL recreate the redo set. If it still fails, use forced recovery to dump data, then rebuild the system tablespace.

---

## Risk-managed runbook tailored to your situation

### Phase 0 — Snapshot and sanity checks
- **Backup datadir:**  
  - `sudo systemctl stop mysql`  
  - `sudo tar -C /var/lib -czf /var/backups/mysql_datadir_$(date +%F_%H%M).tar.gz mysql`
- **Verify ownership:**  
  - `sudo chown -R mysql:mysql /var/lib/mysql`  
  - `sudo chown -R mysql:mysql /var/lib/mysql/#innodb_redo`
- **Confirm disk health:**  
  - `df -h /var /var/lib/mysql`  
  - `sudo dmesg | tail -n 100` (watch for I/O or filesystem errors)

### Phase 1 — Recreate redo logs (low data risk)
- **Move existing redo files aside:**  
  - `sudo mkdir -p /var/lib/mysql/redo_backup_$(date +%F_%H%M)`  
  - `sudo mv /var/lib/mysql/#innodb_redo/#ib_redo* /var/lib/mysql/redo_backup_$(date +%F_%H%M)/`
- **Ensure empty redo dir and perms:**  
  - `sudo chmod 700 /var/lib/mysql/#innodb_redo`  
  - `sudo chown -R mysql:mysql /var/lib/mysql/#innodb_redo`
- **Start server:**  
  - `sudo systemctl start mysql`  
  - Inspect logs: `journalctl -u mysql -n 200 --no-pager` and `sudo tail -n 200 /var/log/mysql/error.log`

Expected outcome: MySQL recreates a fresh, internally consistent redo set and boots. If it still complains about InnoDB/Dictionary, proceed.

### Phase 2 — Forced recovery to salvage data (moderate risk, read-only)
- **Enable forced recovery:**  
  - Edit `/etc/mysql/mysql.conf.d/mysqld.cnf` and add:
    ```
    [mysqld]
    innodb_force_recovery = 1
    ```
  - Try `1`, then `2`, then `3` if needed. Avoid `5/6` unless last resort.
- **Restart and dump:**  
  - `sudo systemctl restart mysql`  
  - `mysqldump --all-databases --single-transaction --quick --routines --events > /var/backups/mysql_full_$(date +%F_%H%M).sql`
- **Remove forced recovery and retry normal start:**  
  - Delete the `innodb_force_recovery` line  
  - `sudo systemctl restart mysql`

### Phase 3 — Rebuild system tablespace (last resort)
- **Move InnoDB core files aside (with service stopped):**  
  - `sudo mv /var/lib/mysql/ibdata1 /var/lib/mysql/ibdata1.bak_$(date +%F_%H%M)`  
  - `sudo mv /var/lib/mysql/ibtmp1 /var/lib/mysql/ibtmp1.bak_$(date +%F_%H%M)`  
  - Ensure `#innodb_redo` is empty
- **Start MySQL to reinitialize system tablespace:**  
  - `sudo systemctl start mysql`
- **Restore from dump:**  
  - Import your `.sql` dump to rebuild user databases.

---

## Why selective deletion won’t help

- **Redo log sequence requirements:** InnoDB tracks the LSN ranges across the ring files; removing one file or attempting to start with an earlier file doesn’t align the internal metadata. The server detected a start LSN expecting `#ib_redo17`; without it, the recovery is undefined and InnoDB aborts.
- **Consistency over partial rollback:** Redo logs aren’t a versioned checkpoint you can “roll back” to; they are a write-ahead log stream used to replay to a consistent state.

---

## Hardening after recovery

- **Ensure crash-safety settings:**  
  - **innodb_flush_log_at_trx_commit=1** for durability  
  - **sync_binlog=1** if binary logging is enabled
- **Validate AppArmor paths if you moved datadir:**  
  - Update `/etc/apparmor.d/usr.sbin.mysqld` and reload: `sudo apparmor_parser -r /etc/apparmor.d/usr.sbin.mysqld`
- **Implement health and alerts:**  
  - **Startup check:** `mysqladmin ping -uroot -p` in a systemd unit or cron  
  - **Log monitor:** alert on `[InnoDB] Missing redo log file` and `[Server] Failed to initialize DD Storage Engine`  
  - **Disk monitoring:** free space and inode thresholds for `/var` and datadir

---

## Quick questions to tailor this further

- Do you have any backup of `/var/lib/mysql/#innodb_redo` from before Oct 2?  
- Did the datadir or MySQL version change around Oct 2–Oct 9?  
- Can you share the last 100 lines of `/var/log/mysql/error.log` after Phase 1?

If you’re aligned, I’ll produce a copy-paste script that performs Phase 0–1 safely, logs each step, and aborts on any unexpected state, so you can run it once and get a clear success/failure verdict.

---

https://copilot.microsoft.com/shares/FuQ1rUxCu989VpfpLGZjp
