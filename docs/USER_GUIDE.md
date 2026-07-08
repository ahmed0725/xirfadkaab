# Xirfad Kaab — User Guide

This guide explains how to use the application **after you have an account**. Capabilities depend on your **role**: **Administrator**, **Staff (User)**, or **Teacher**.

---

## 1. Signing in and profile

1. Open your organization’s application URL.
2. Enter your **email** and **password**.
3. Use **Dashboard** in the sidebar to return to the home summary after login.
4. **Settings** (sidebar bottom) opens your profile: you can update your name, email, and password.

If you forget your password, use “Forgot password?” on the login screen **if** your administrator has configured outgoing mail.

---

## 2. Understanding roles

### Administrator

- Full access to **Course types**, **Users**, **System settings**, **Reports**, and all **financial** actions (recording tuition, additional charges, expenses, inventory changes).
- Only administrators can **delete** students, classes, subjects, fee records, and some other destructive actions.

### Staff (“User” role)

- Manage **students** (register and edit; **cannot delete** students — ask an administrator).
- Create and edit **classes** and **subjects** (**cannot delete** classes or subjects — administrator only).
- View **Fees**, **Expenses**, and **Inventory** listings.
- Create and manage **exams** (depending on menu access).
- **Cannot** manage **course types**, **users**, **system settings**, or **reports**.
- **Cannot** record new tuition payments or edit fee entries — those screens are reserved for administrators.

### Teacher

- View **Classes** you are **assigned to** (you will not see other classes in the list).
- Open **Exams** to view exams and enter **results** for students where the system allows.
- Use **Attendance** to view history and **mark attendance** only for **your assigned classes**.

If something is missing from your menu, your account may need a different role or **class assignment** — contact an administrator.

---

## 3. Dashboard

The dashboard gives a quick overview (counts and shortcuts). Use the sidebar for detailed modules.

---

## 4. Course types (Administrators only)

**Course types** describe broad skill or program categories (for example: Electrical, Plumbing). Classes must use a course type so similar class names can exist at different times without confusion.

1. Go to **Course types** in the sidebar.
2. Click **Add course type**, enter a **name**, and save.
3. To change a name, use **Edit**.
4. You **cannot delete** a course type that is still linked to classes; reassign or remove classes first.

---

## 5. Classes

### Who can do what

| Action | Administrator | Staff | Teacher |
|--------|:-------------:|:-----:|:-------:|
| View class list & details | Yes | Yes | Yes — **assigned classes only** |
| Add / edit class | Yes | Yes | No |
| Delete class | Yes | No | No |

### Adding or editing a class (Administrator or Staff)

1. Open **Classes** → **Add Class** (or **Edit** from the list or detail page).
2. Fill in:
   - **Class name** — e.g. “Electrical” (multiple cohorts can share the same name).
   - **Course type** — choose from the list managed under Course types.
   - **Start date** and **Duration (months)** — the **end date** is calculated automatically.
   - **Class time** — when the session runs (e.g. 4:00 PM).
   - **Shift** — Morning, Afternoon, or Evening.
   - **Classroom** — optional.
   - **Default monthly fee** — used as a baseline for fees where applicable.
   - **Teachers** — optional multi-select; holds **Ctrl** (Windows) or **Cmd** (Mac) to pick several teachers.
   - **Class is active** — inactive classes may be hidden from new student enrollment depending on policy.

3. Save.

### Finding a class

On **Classes**, use **filters**: search by name, course type, shift, time, or active/inactive status.

### Teachers viewing classes

Teachers use the same **Classes** menu but only see classes where they were assigned on the class **Edit** screen.

---

## 6. Students

1. Open **Students**.
2. **Add** a student with required details and assign a **class**. Student IDs may be generated automatically.
3. Use **search** and filters on the list to find someone.
4. **Delete** student records — **administrator only**.

Student registration dates and status (e.g. active/inactive) help filter lists and reports.

---

## 7. Subjects

Subjects belong to a **specific class**. Add subjects so attendance or exams can optionally be tied to a subject.

1. **Subjects** → **Add Subject**.
2. Choose the **class**, then enter the **subject name**.
3. Subject names must be unique **within that class**.

Deleting subjects is limited to **administrators**.

---

## 8. Attendance

### Marking daily attendance (Administrators, Staff, Teachers)

1. Go to **Attendance** → **Mark Attendance** (or the equivalent button from the attendance list).
2. Choose the **date**.
3. Select **one class**. Only after you choose a class does the student list load — you **never** mark all students in the school at once from this screen.
4. Optionally choose a **subject** if subjects exist for that class.
5. Set each student to **Present**, **Absent**, or **Late**, and add notes if needed.
6. Use **Mark all present** if everyone should default to present (you can still change individuals).
7. Click **Save Attendance**.

**Teachers:** You only see classes you are assigned to. You cannot submit attendance for another class.

### History and corrections

- **Attendance** list shows past records; filter by date or class where available.
- **Edit** a single record if a mistake was made.
- **Deleting** an attendance row is typically **administrator only**.

---

## 9. Fees (tuition and additional charges)

### Viewing

- **Fees** shows monthly tuition-style records and summaries. **Staff** and **Teachers** can usually **view**; exact filters depend on your installation.

### Recording payments (Administrators)

- **Record tuition payment** and **additional charges** (books, certificates, etc.) are **administrator** actions.
- Receipts can often be opened or printed from the fee lists.

If you are staff and need a payment recorded, ask an **administrator**.

---

## 10. Exams

- **Exams** lists scheduled exams; open an exam to see details.
- **Teachers** (and staff where permitted) can enter **marks / results** on the exam detail flow after the exam exists.
- Creating or editing **exam definitions** (title, date, class, subject) is done by **staff/administrator** roles according to your menu.

---

## 11. Expenses and inventory

- **Expenses** and **Inventory** sections let authorized users **view** lists and details.
- **Creating**, **editing**, or **deleting** expense or inventory entries is normally **administrator only**.

---

## 12. Reports (Administrators)

**Reports** lets administrators generate summaries (students, attendance, fees, exams, etc.), print views, and export PDFs depending on options shown on screen. Choose **report type**, **period**, and filters (class, student, etc.), then generate or download.

---

## 13. System settings (Administrators)

**System settings** stores organization-wide options such as school name and address used on receipts and reports. Only administrators should change these values.

---

## 14. Users (Administrators)

Under **Users**, administrators create accounts and set each person’s **role** (Administrator, User, or Teacher). New teachers must still be **assigned to classes** on the **class edit** screen so they can see those classes and take attendance.

---

## 15. Tips for smooth daily use

1. **Set up course types first**, then classes, then assign **teachers** to each class.
2. Enroll **students** in the correct class; class lists in attendance depend on that assignment.
3. Use **display names** in dropdowns (class name + course type + time + shift) to pick the right cohort when names overlap.
4. If a teacher sees **no classes**, verify **teacher assignment** on each class.

---

## 16. Getting help

- Application errors or blank pages: contact your administrator with **what you clicked** and **approximately when** it happened.
- Permission issues (403 / missing menu): your **role** or **assignments** may need updating — administrators handle this under **Users** and **Classes**.

For installation, hosting, and backups, technical staff should read [SYSTEM_MANUAL.md](SYSTEM_MANUAL.md).
