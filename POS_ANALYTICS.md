# POS Sales Analytics Documentation

## Overview

The POS Sales Analytics feature provides comprehensive insights into Point of Sale performance, helping businesses make data-driven decisions. This feature is accessible from the Reports page and offers detailed metrics, trends, and performance indicators.

## Access & Permissions

-   **Location**: Reports page → POS Analytics tab
-   **Required Role**: Admin or Manager
-   **Navigation**: Sidebar → Reports → POS Analytics tab

## Features

### 1. Key Metrics Dashboard

Four primary metrics displayed in cards at the top of the page:

-   **Total POS Revenue**: Sum of all completed POS sales (excluding refunds) in the selected date range
-   **Transactions**: Total number of completed sales transactions
-   **Average Transaction**: Average sale amount (Total Revenue / Transaction Count)
-   **Items Sold**: Total quantity of items sold across all transactions

### 2. Revenue by Payment Method

Visual breakdown of revenue and transaction count by payment method:

-   **Methods Tracked**:

    -   Cash (Green indicator)
    -   Card (Blue indicator)
    -   Mobile Money (Purple indicator)
    -   Bank Transfer (Orange indicator)

-   **Display Elements**:
    -   Color-coded indicators for visual distinction
    -   Transaction count badges
    -   Total revenue per method
    -   Percentage bars showing distribution
    -   Percentage of total transactions

### 3. Daily Sales Trend

Chronological view of sales performance:

-   Daily breakdown of transactions and revenue
-   Visual progress bars proportional to daily totals
-   Date labels in "Month Day" format
-   Transaction count for each day
-   Total revenue for each day

### 4. Top Products Analysis

Two separate rankings:

#### Top Products by Quantity

-   Ranks products by units sold
-   Shows product name and SKU
-   Displays quantity sold
-   Includes revenue generated

#### Top Products by Revenue

-   Ranks products by revenue generated
-   Shows product name and SKU
-   Displays total revenue (highlighted in emerald)
-   Includes quantity sold

Both tables limit to top 10 products.

### 5. Performance Metrics

#### Sales by Hour

-   Groups sales by hour of the day (00:00 to 23:00)
-   Shows transaction count per hour
-   Visual bar charts with transaction counts
-   Total revenue per hour
-   Helps identify peak business hours

#### Sales by Day of Week

-   Groups sales by day (Monday to Sunday)
-   Transaction count per day
-   Total revenue per day
-   Average transaction value per day
-   Sorted by highest revenue

### 6. Top Customers

Ranks customers by spending:

-   Customer name and email
-   Total transactions
-   Total spent (highlighted in emerald)
-   Average transaction value
-   Limited to top 10 customers
-   Only includes sales with associated customers

### 7. Additional Financial Metrics

Three supplementary cards:

-   **Total Discounts**: Sum of all discount amounts given
-   **Cash Sales**: Total from cash payment method only
-   **Card/Digital Sales**: Combined total from Card, Mobile Money, and Bank Transfer

## Date Range Filtering

All analytics respect the selected date range:

-   Date pickers for Start Date and End Date
-   Quick filter buttons:
    -   "This Month" - Current month to date
    -   "Last Month" - Previous calendar month
-   Default: Current month
-   Validation ensures End Date is after Start Date

## Data Filters & Exclusions

### Included Data

-   Sales with status = "Completed"
-   Sales within the selected date range
-   Sales from all payment methods

### Excluded Data

-   Refunded sales (status = "Refunded")
-   Sales outside the date range
-   Walk-in customers appear as "Walk-in Customer" in top customers (if customer_id is null)

## Technical Implementation

### Backend Logic

Located in: `app/Livewire/Reports/Index.php`

**Key Method**: `getPosData()`

**Data Queries**:

1. **Basic Metrics**: Collection-based aggregation using Laravel Collections
2. **Top Products**: Raw SQL with JOINs for performance (grouped by inventory_item_id)
3. **Sales by Hour**: Groups by hour extracted from sale_date
4. **Sales by Day of Week**: Groups by day name from sale_date
5. **Top Customers**: Groups by customer_id with relationship loading

**Performance Optimizations**:

-   Eager loading of relationships (customer, soldBy, items.inventoryItem)
-   Database-level aggregations using DB::raw()
-   Indexed columns (sale_date, status) for faster filtering
-   Limited result sets (top 10) for heavy queries

### Frontend Display

Located in: `resources/views/livewire/reports/index.blade.php`

**UI Components**:

-   Flux UI components for consistent design
-   Responsive grid layouts (2-3 columns)
-   Progress bars for visual data representation
-   Color-coded badges and indicators
-   Empty states for no-data scenarios
-   Emerald color scheme for revenue highlights

**Responsive Breakpoints**:

-   Mobile: Single column stacks
-   Tablet (sm): 2 columns
-   Desktop (lg): 3-4 columns
-   XL screens: Maximum width optimizations

## Testing

Test Suite: `tests/Feature/Reports/PosAnalyticsTest.php`

**Test Coverage** (15 tests):

1. Key metrics display
2. Revenue by payment method
3. Daily sales trend
4. Top products by quantity
5. Top products by revenue
6. Sales by hour
7. Sales by day of week
8. Top customers
9. Discount totals
10. Cash vs digital sales separation
11. Date range filtering
12. Refunded sales exclusion
13. No data graceful handling
14. Average transaction calculation
15. Payment method percentages

**Test Results**: ✅ All 15 tests passing

## Usage Examples

### Scenario 1: Identifying Peak Hours

1. Navigate to Reports → POS Analytics
2. Scroll to "Sales by Hour" section
3. Identify hours with highest bars
4. Use insights to schedule staff appropriately

### Scenario 2: Analyzing Top Sellers

1. View "Top Products (Quantity)" for best movers
2. View "Top Products (Revenue)" for most profitable
3. Compare both lists to identify:
    - High-volume low-margin items
    - Low-volume high-margin items
4. Adjust inventory and promotions accordingly

### Scenario 3: Payment Method Trends

1. Review "Revenue by Payment Method" section
2. Note percentage distribution
3. Identify preferred customer payment methods
4. Optimize payment infrastructure (e.g., more card terminals if card usage is high)

### Scenario 4: Customer Loyalty Analysis

1. Review "Top Customers" section
2. Identify high-value customers
3. Calculate average transaction values
4. Create targeted retention strategies

### Scenario 5: Weekly Performance

1. Set date range to last week
2. Review "Sales by Day of Week"
3. Identify strongest and weakest days
4. Plan promotions for slow days

## Key Insights Provided

1. **Operational Efficiency**

    - Peak hours for staffing decisions
    - Daily patterns for scheduling
    - Transaction volume trends

2. **Financial Performance**

    - Revenue trends over time
    - Payment method preferences
    - Discount impact on revenue

3. **Product Management**

    - Best-selling products
    - High-revenue generators
    - Stock prioritization insights

4. **Customer Behavior**

    - Top spenders identification
    - Average purchase values
    - Payment preferences

5. **Business Health**
    - Day-of-week performance
    - Revenue consistency
    - Growth patterns

## Future Enhancements

Potential additions for future versions:

1. **Comparative Analytics**

    - Period-over-period comparisons
    - Year-over-year growth metrics
    - Trend indicators (up/down arrows)

2. **Advanced Visualizations**

    - Interactive charts (Chart.js integration)
    - Pie charts for payment distribution
    - Line graphs for trend analysis

3. **Export Functionality**

    - PDF report generation
    - Excel export for analysis
    - Email scheduled reports

4. **Custom Date Ranges**

    - "Last 7 days" quick filter
    - "This Quarter" option
    - Custom date presets

5. **Drill-Down Capabilities**

    - Click product to see detailed sales
    - Click customer to view purchase history
    - Click hour to see transactions

6. **Predictive Analytics**
    - Sales forecasting
    - Stock prediction
    - Seasonal trend identification

## Best Practices

1. **Regular Review**: Check analytics weekly to identify trends early
2. **Date Range Selection**: Use consistent periods for comparison
3. **Action Items**: Create specific tasks based on insights
4. **Staff Training**: Ensure team understands metrics and their importance
5. **Benchmark Setting**: Establish KPIs and track progress

## Troubleshooting

### Issue: No data showing

-   **Solution**: Check date range selection, ensure sales exist in that period

### Issue: Unexpected numbers

-   **Solution**: Verify status filters, confirm sales aren't refunded

### Issue: Missing customers in top customers list

-   **Solution**: Customers only appear if customer_id is set on sales

### Issue: Slow loading

-   **Solution**: Reduce date range, large datasets may take longer to process

## Integration Points

This feature integrates with:

1. **POS Sales System**: Source of all data
2. **Inventory Management**: Product names and SKUs
3. **Customer Management**: Customer information for top spenders
4. **User Management**: Authorization via UserPolicy

## Database Tables Used

-   `pos_sales`: Main sales data
-   `pos_sale_items`: Line item details
-   `inventory_items`: Product information
-   `customers`: Customer details
-   `users`: Sales staff information

## Color Scheme

Consistent with POS system design:

-   **Emerald (600)**: Revenue, success, positive metrics
-   **Green (500/600)**: Cash, growth
-   **Blue (500)**: Card payments
-   **Purple (500)**: Mobile Money
-   **Orange (500)**: Bank Transfer
-   **Amber (600)**: Discounts, warnings
-   **Zinc**: Neutral UI elements

## Accessibility

-   Clear headings and labels
-   Color coding supplemented with text
-   Responsive design for all devices
-   Empty states with helpful messages
-   Loading states during data fetch

## Performance Considerations

-   **Caching**: Consider implementing for large datasets
-   **Pagination**: Top 10 limits prevent overwhelming displays
-   **Indexing**: Database indexes on frequently queried columns
-   **Eager Loading**: Prevents N+1 query problems
-   **Date Boundaries**: Queries limited by date range

## Version History

### Version 1.0.0 (October 11, 2025)

-   Initial release of POS Analytics
-   15 comprehensive tests
-   Full feature set with 7 main sections
-   Responsive UI with Flux components
-   Integration with existing Reports page

---

**Documentation Last Updated**: October 11, 2025  
**Feature Status**: ✅ Production Ready  
**Test Coverage**: 100% (15/15 tests passing)
