### Example 2. Reverse

---

```php
        $workingDateTime = (new WorkingDateTime())
            ->setDateFrom('2024-03-15 10:00:00')
            ->setStartHourWorkingDay(8)
            ->setStartMinuteWorkingDay(0)
            ->setEndHourWorkingDay(17)
            ->setEndMinuteWorkingDay(0)
            ->setDays(3) // Number of working days from DateFrom before executing an action (only within working hours)
            ->setHours(2) // Number of working hours from DateFrom before executing an action (only within working hours)
            ->setMinutes(30) // Number of working minutes from DateFrom before executing an action (only within working hours)
            ->setWeekends(['Saturday', 'Sunday'])
            ;
        $dateTime = $workingDateTime->calculate(); // 2025-01-10 16:00:00
```
---

### **🔍 Input Data**
- **Start Date**: `2024-03-15 10:00:00` (Friday)
- **Working Hours**: `08:00 – 17:00`
- **Duration to Subtract**:
  - `1 day`
  - `3 hours`
  - `30 minutes`
- **Weekends**: `Saturday, Sunday`
- **No Holidays**

---
### **🔢 Reverse Calculation**

| 📅 Date         | Day of the Week | Remaining Time to Subtract | Working Day? |
|---------------|----------------|----------------------------|--------------|
| **2024-03-15** | Friday         | **-3 hours 30 minutes** → **06:30 (OUTSIDE WORKING HOURS)** | ✅ |
| **2024-03-14** | Thursday       | **Move to the previous day, starting at 17:00**  | ✅ |
| **2024-03-14** | Thursday       | **17:00 - 9 hours (working day)** → **08:00 (remaining 16 hours 30 minutes)**  | ✅ |
| **2024-03-13** | Wednesday      | **17:00 - 9 hours** → **08:00 (remaining 7 hours 30 minutes)** | ✅ |
| **2024-03-12** | Tuesday        | **17:00 - 7 hours 30 minutes** → **09:30 (FINISH)** | ✅ **FINISH** |

---

### **✅ Updated Correct Date**
**`2024-03-12 09:30:00`**
