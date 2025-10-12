# POS Analytics - Visual Layout Guide

## Page Structure

```
┌────────────────────────────────────────────────────────────────────────┐
│                    Reports & Analytics                                  │
│              Business insights and performance metrics                  │
├────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌──────────────┬──────────────┬──────────────┬──────────────┐        │
│  │ Start Date   │ End Date     │ [This Month] │ [Last Month] │        │
│  │ [          ] │ [          ] │              │              │        │
│  └──────────────┴──────────────┴──────────────┴──────────────┘        │
│                                                                          │
├────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────────┐  │
│  │ Sales Report │ Payment History │ [POS Analytics] │ Technician │  │
│  │              │                 │                  │ Performance│  │
│  │ Inventory    │                                                    │  │
│  └─────────────────────────────────────────────────────────────────┘  │
│                                                                          │
└────────────────────────────────────────────────────────────────────────┘
```

## POS Analytics Tab Content

### 1. Key Metrics (Top Section)

```
┌────────────────┬────────────────┬────────────────┬────────────────┐
│ Total POS      │ Transactions   │ Avg Transaction│ Items Sold     │
│ Revenue        │                │                │                │
│                │                │                │                │
│ $12,450.00     │     248        │   $50.20       │     1,234      │
│ (emerald)      │                │                │                │
└────────────────┴────────────────┴────────────────┴────────────────┘
```

### 2. Revenue by Payment Method

```
┌──────────────────────────────────────────────────────────────┐
│  Revenue by Payment Method                                    │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│  ● Cash                         [124 transactions]  $6,200.00 │
│  ████████████████████████████████ 50.0%                      │
│                                                                │
│  ● Card                         [74 transactions]   $3,700.00 │
│  ██████████████████ 30.0%                                     │
│                                                                │
│  ● Mobile Money                 [37 transactions]   $1,850.00 │
│  ██████████ 15.0%                                             │
│                                                                │
│  ● Bank Transfer                [13 transactions]   $700.00   │
│  ████ 5.0%                                                    │
│                                                                │
└──────────────────────────────────────────────────────────────┘
```

### 3. Daily Sales Trend

```
┌──────────────────────────────────────────────────────────────┐
│  Daily Sales Trend                                            │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│  Oct 01       15 sales       ████████████████████ $1,200.00  │
│  Oct 02       23 sales       ███████████████████████ $1,450.00│
│  Oct 03       18 sales       ██████████████████ $1,100.00    │
│  Oct 04       27 sales       ████████████████████████ $1,650.00│
│  Oct 05       19 sales       ███████████████████ $1,250.00   │
│  ...                                                           │
│                                                                │
└──────────────────────────────────────────────────────────────┘
```

### 4. Top Products (Side by Side)

```
┌──────────────────────────────┬──────────────────────────────┐
│  Top Products (Quantity)     │  Top Products (Revenue)      │
├──────────────────────────────┼──────────────────────────────┤
│ Product           Qty Revenue│ Product           Revenue Qty│
│ ────────────────────────────│ ────────────────────────────│
│ iPhone Cable       245  $980 │ Laptop Charger   $2,400  120│
│ SKU: ACC-001                 │ SKU: ACC-005                 │
│                              │                              │
│ Phone Case         198  $792 │ Power Bank       $1,980   99│
│ SKU: ACC-002                 │ SKU: ACC-006                 │
│                              │                              │
│ Screen Protector   156  $624 │ Wireless Mouse   $1,680  120│
│ SKU: ACC-003                 │ SKU: ACC-007                 │
│                              │                              │
│ ...                          │ ...                          │
└──────────────────────────────┴──────────────────────────────┘
```

### 5. Performance Metrics

```
┌──────────────────────────────┬──────────────────────────────┐
│  Sales by Hour               │  Sales by Day of Week        │
├──────────────────────────────┼──────────────────────────────┤
│ 08:00  ███ 12 sales  $480    │ Friday        45 sales       │
│ 09:00  ████████ 24   $960    │               $2,250.00      │
│ 10:00  ██████████ 28 $1,120  │ Avg: $50.00 per transaction  │
│ 11:00  ████████ 23   $920    │                              │
│ 12:00  ██████ 18     $720    │ Saturday      38 sales       │
│ 13:00  ██████ 19     $760    │               $1,900.00      │
│ 14:00  ████████████ 31 $1240 │ Avg: $50.00 per transaction  │
│ 15:00  ██████████ 27  $1080  │                              │
│ ...                          │ Thursday      35 sales       │
│                              │               $1,750.00      │
│                              │ Avg: $50.00 per transaction  │
│                              │                              │
│                              │ ...                          │
└──────────────────────────────┴──────────────────────────────┘
```

### 6. Top Customers

```
┌──────────────────────────────────────────────────────────────┐
│  Top Customers                                                │
├──────────────────────────────────────────────────────────────┤
│ Customer         Trans   Total Spent      Avg Transaction    │
│ ──────────────────────────────────────────────────────────── │
│ John Doe           12     $1,200.00           $100.00        │
│ john@email.com                                                │
│                                                                │
│ Jane Smith          8     $960.00             $120.00        │
│ jane@email.com                                                │
│                                                                │
│ Bob Johnson         7     $840.00             $120.00        │
│ bob@email.com                                                 │
│                                                                │
│ ...                                                            │
└──────────────────────────────────────────────────────────────┘
```

### 7. Additional Financial Metrics

```
┌────────────────┬────────────────┬────────────────┐
│ Total Discounts│ Cash Sales     │ Card/Digital   │
│                │                │ Sales          │
│                │                │                │
│ $425.00        │ $6,200.00      │ $6,250.00      │
│ (amber)        │ (green)        │ (blue)         │
└────────────────┴────────────────┴────────────────┘
```

## Color Legend

### Payment Methods

-   🟢 **Cash** - Green (#10b981)
-   🔵 **Card** - Blue (#3b82f6)
-   🟣 **Mobile Money** - Purple (#a855f7)
-   🟠 **Bank Transfer** - Orange (#f97316)

### Metrics

-   🟢 **Emerald** - Revenue, positive metrics (#059669)
-   🟡 **Amber** - Discounts, warnings (#d97706)
-   ⚪ **Zinc** - Neutral UI elements (#71717a)

### UI States

-   **Progress Bars** - Emerald fill on zinc background
-   **Badges** - Small pills with payment method or count
-   **Cards** - White background with border, shadow on hover
-   **Tables** - Zebra striping on hover

## Responsive Behavior

### Mobile (< 640px)

-   Single column layout
-   Cards stack vertically
-   Tables become scrollable
-   Progress bars full width

### Tablet (640px - 1024px)

-   2 column grid
-   Side-by-side cards where appropriate
-   Reduced padding
-   Optimized table widths

### Desktop (> 1024px)

-   3-4 column grid for metrics
-   Full side-by-side layouts
-   Maximum visual hierarchy
-   Optimal spacing

## Empty States

When no data is available:

```
┌──────────────────────────────────────────────────────────────┐
│                                                                │
│                    🔍                                         │
│                                                                │
│            No POS sales data for selected period              │
│                                                                │
│         Try adjusting your date range to see data             │
│                                                                │
└──────────────────────────────────────────────────────────────┘
```

## Interactive Elements

1. **Date Range Pickers** - Calendar dropdowns for date selection
2. **Quick Filters** - Clickable buttons for common ranges
3. **Tab Navigation** - Switch between report types
4. **Hover States** - Cards and rows highlight on hover
5. **Empty State Messages** - Helpful guidance when no data

## Accessibility Features

-   Semantic HTML structure
-   ARIA labels for screen readers
-   Color coding supplemented with text
-   Keyboard navigation support
-   Focus indicators on interactive elements
-   High contrast text on backgrounds

## Print Optimization

When printing or exporting:

-   Remove unnecessary UI chrome
-   Optimize for A4/Letter paper
-   Black and white friendly
-   Page breaks at logical sections
-   Headers and footers with date range

---

**Note**: This is a text-based representation. The actual implementation uses Flux UI components with Tailwind CSS for a modern, responsive design.
