### Example 1. Default

---

```php
        $workingDateTime = (new WorkingDateTime())
            ->setDateFrom('2024-12-30 14:00:00')
            ->setStartHourWorkingDay(8)
            ->setStartMinuteWorkingDay(0)
            ->setEndHourWorkingDay(17)
            ->setEndMinuteWorkingDay(0)
            ->setDays(3) // Number of working days from DateFrom before executing an action (only within working hours)
            ->setHours(2) // Number of working hours from DateFrom before executing an action (only within working hours)
            ->setWeekends(['Saturday', 'Sunday'])
            ;
        $dateTime = $workingDateTime->calculate(); // 2025-01-10 16:00:00
```
---

# 📅 **Working Time Calculation**

## **📌 Given:**
- **Start Time:** `2024-12-30 14:00:00`
- **Working Hours:** `08:00 - 17:00` (**9 hours per day**)
- **Weekends:** `Saturday, Sunday`
- **Holiday:** `2025-01-01`
- **Add:** `3 days 2 hours`
- **Total Working Time in Seconds:** `255600 sec` (**71 hours**)

---

### **🔍 Step 1: Calculating Available Time on the First Day**
- Start at **2024-12-30 14:00**
- Time left until the end of the working day: **3 hours** (`17:00 - 14:00`)
- **Remaining hours after the first day:** `71 - 3 = 68 hours`

---

### **🔍 Step 2: Distributing Work Hours (9 hours per working day)**
There are `68 hours` left. We distribute them across working days (**9 hours per day**):
- `68 / 9 ≈ 7.56 working days` (**7 full days + 5 hours remaining**)

---

### **🔍 Step 3: Considering Weekends & Holidays**
We distribute the working days, skipping weekends (`Saturday, Sunday`) and the holiday (`01-01`):

| 📅 Date        | Day of the Week | Remaining Hours | Working Day? |
|---------------|----------------|----------------|--------------|
| **2024-12-30** | Monday         | 68 → 65 (**worked 3 hours**) | ✅ |
| **2024-12-31** | Tuesday        | 65 → 56 (**worked 9 hours**) | ✅ |
| **2025-01-01** | Wednesday      | **Skipped (Holiday 🎉)** | ❌ |
| **2025-01-02** | Thursday       | 56 → 47 (**worked 9 hours**) | ✅ |
| **2025-01-03** | Friday         | 47 → 38 (**worked 9 hours**) | ✅ |
| **2025-01-04** | Saturday       | **Skipped (Weekend)** | ❌ |
| **2025-01-05** | Sunday         | **Skipped (Weekend)** | ❌ |
| **2025-01-06** | Monday         | 38 → 29 (**worked 9 hours**) | ✅ |
| **2025-01-07** | Tuesday        | 29 → 20 (**worked 9 hours**) | ✅ |
| **2025-01-08** | Wednesday      | 20 → 11 (**worked 9 hours**) | ✅ |
| **2025-01-09** | Thursday       | 11 → 2 (**worked 9 hours**) | ✅ |
| **2025-01-10** | Friday         | 2 → **0** (**worked 2 hours**) | ✅ **FINISHED** |

---

## **✅ Conclusion:**
- The final date is **2025-01-10 16:00:00**.
