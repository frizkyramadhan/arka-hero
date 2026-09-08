# Glossary

Terms locked while designing employee mutation and annual leave.

| Term | Meaning |
| --- | --- |
| **DOH** | Date of Hire on an `administrations` row. Original hire / placement date. Not a transfer date. |
| **Service Start DOH** | First DOH in the current continuous service period. EOC rehire continues; other terminations reset. Used for years of service and LSL. |
| **Mutasi** | Project transfer without ending employment. Stored in `employee_mutations` as date, destination project, status, remarks. An employee may have many rows. Only **active** rows count for leave. |
| **Tanggal Acuan Cuti Tahunan** | Anniversary for Group 1 annual leave period: latest mutasi date, else Service Start DOH. |
| **Project mutasi** | Destination project on the latest mutasi. Leave entitlement settings (roster vs non-roster, eligible types) follow this project. Active administration project is synced to it. |
| **PAR mutasi** | Letter Number PAR type. Not this employment history. |
