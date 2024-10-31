<?php
/**
 * PHP Mortgage Calculator
 *
 * Copyright 2002-2008 Dave Tufts <dave@imarc.net>
 *
 * @version 2.0.0
 *
 * @author  Dave Tufts [dt] <dave@imarc.net>
 *
 * @changes 2.0.0 Major refactoring and code cleanup; added customizable property tax, assessed value, condo fee [dt, 2008-03-15]
 * @changes 1.3.0 Updated CSS [dt, 2007-08-21]
 * @changes 1.2.1 fixed bug with uninitialize variable $pmi_per_month, $pmi_text [dt, 2007-01-04]
 * @changes 1.2.0 fixed bug with number_format, clead up comments [dt, 2006-02-16]
 * @changes 1.1.0 initial release [dt, 2003-01-01]
 */

class MortgageCalculator {
	
	private $monthTerm; // Number of months ($yearTerm x 12)
	private $pmiPerMonth;
	private $monthlyFinanceTotal; // Total Monthly Payment
	private $downPayment;
	private $financingPrice;
	private $annualInterestRate;
	private $monthlyInterestRate;
	private $propertyYearlyTax;
	private $propertyMontlyTax;
	private $totalMonthlyBill;
	
	public function __construct($salePrice, $interestRate, $yearTerm, $downPercent, $propertyTaxRate, $condoFee, $assessedValue) {
		if (($yearTerm <= 0) || ($salePrice <= 0) || ($interestRate <= 0)) throw new Exception ( 'You must enter a <strong>Sale Price</strong>, <strong>Length of Mortgage</strong> and <strong>Annual Interest Rate</strong>');
		if ($this->assessedValue <= 0 && $this->salePrice > 0) $this->assessedValue = $this->salePrice * .85;
		$this->monthTerm = $yearTerm * 12;
		$this->downPayment = $salePrice * ($downPercent / 100);
		$this->financingPrice = $salePrice - $this->downPayment;
		$this->annualInterestRate = $interestRate / 100;
		$this->monthlyInterestRate   = $this->annualInterestRate / 12;
		$monthlyFinanceTotal = $this->financingPrice / $this->_get_interest_factor($yearTerm, $this->monthlyInterestRate);
		$this->propertyYearlyTax = ($assessedValue / 1000) * $propertyTaxRate;
		$propertyMonthlyTax = $this->propertyYearlyTax / 12;
		if ($downPercent < 20) $this->pmiPerMonth = 55 * ($this->financingPrice / 100000);
		// Total principal, interest, pmi, taxes, fees
		$this->totalMonthlyBill  = $this->monthlyFinanceTotal + $this->pmiPerMonth + $this->propertyMonthlyTax + $condoFee;
	}
	
	public function calculate() {

	}
	
	/**
	 * Calculates actual mortgage calculations by plotting a PVIFA table
	 * (Present Value Interest Factor of Annuity)
	 *
	 * @param  float  length, in years, of mortgage
	 * @param  float  monthly interest rate
	 * @return float  denominator used to calculate monthly payment
	 */
	function _get_interest_factor($year_term, $monthly_interest_rate) {	
		$factor      = 0;
		$base_rate   = 1 + $monthly_interest_rate;
		$denominator = $base_rate;
		for ($i=0; $i < ($year_term * 12); $i++) {
			$factor += (1 / $denominator);
			$denominator *= $base_rate;
		}
		return $factor;
	}
	
	/**
	 * Formats input as string of money ($n.nn)
	 *
	 * @param  float  number
	 * @return string number formatted as US currency
	 */
	function _money($input) {return '$' . number_format($input, "2", ".", ","); }
	
	/**
	 * Cleans input from any non-float charachters
	 *
	 * @param  mixed Any string or number
	 * @return float
	 */
	function _clean_number($input) {return (float) preg_replace('/[^0-9.]/', '', $input);}
	
	
}



/* --------------------------------------------------------------------- */
/* VARIABLES
/* --------------------------------------------------------------------- */
$sale_price                      = (float) _clean_number(_request('sale_price', 200000));
$mortgage_interest_percent       = (float) _clean_number(_request('mortgage_interest_percent', 6.5));
$year_term                       = (float) _clean_number(_request('year_term', 30));
$down_percent                    = (float) _clean_number(_request('down_percent', 10));
$assessed_value                  = (float) _clean_number(_request('assessed_value'));
$property_tax_rate               = (float) _clean_number(_request('property_tax_rate', 14));
$condo_fee                       = (float) _clean_number(_request('condo_fee'));

$show_progress                   = (bool)  _request('show_progress', true);
$form_complete                   = (bool)  _request('form_complete', false);
$pmi_per_month                   = 0;
$total_monthly_bill              = 0;
$month_term                      = $year_term * 12;
	

?>

<h2>Purchase &amp; Financing Information</h2>
<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	
	<?php if ($total_monthly_bill > 0) { ?>
		<div class="total">
			Your total monthly payment is 
			<strong><?php echo _money($total_monthly_bill) ?></strong> 
			<a href="#total_details">[Details]</a>
		</div>
	<?php } ?>
		
	<table cellpadding="0" cellspacing="0" class="input">
		<tr class="<?php echo _get_background() ?>">
			<th>Sale Price of Home:</th>
			<td><input type="text" size="12" name="sale_price" value="<?php echo _money($sale_price); ?>" /> (dollars)</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Percentage Down:</th>
			<td><input type="text" size="5" name="down_percent" value="<?php echo $down_percent; ?>" />%</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Mortgage Interest Rate:</th>
			<td><input type="text" size="5" name="mortgage_interest_percent" value="<?php echo $mortgage_interest_percent; ?>" />%</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Length of Mortgage:</th>
			<td><input type="text" size="3" name="year_term" value="<?php echo $year_term; ?>" /> years</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Assessed Home Value:</th>
			<td>
				<input type="text" size="12" name="assessed_value" value="<?php echo _money($assessed_value); ?>" /> (dollars)<br />
				<p class="info">
					The assessed value is used to compute property taxes.
					On average, properties are assessed at about 85% of their 
					selling price. If you know the actual assessed value for 
					this property, enter it here. If not, <strong>leave zero  
					and we'll use 85% of the sale price</strong>.
				</p>
			</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Property Tax Rate:</th>
			<td>
				<input type="text" size="3" name="property_tax_rate" value="<?php echo $property_tax_rate; ?>" /> (dollars per $1000)
				<p class="info">
					Property tax rates vary between states and towns. The US average is about $13.80 for every $1000 of the assessed home value. 
				</p>
			</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Condo/Monthly Fee(s):</th>
			<td><input type="text" size="3" name="condo_fee" value="<?php echo $condo_fee; ?>" /> (dollars)</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Explain Calculations:</th>
			<td><input type="checkbox" name="show_progress" value="1" <?php if ($show_progress) { print("checked=\"checked\""); } ?> /> Show the calculations and amortization</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>&nbsp;</th>
			<td>
				<input type="hidden" name="form_complete" value="1" />
				<input type="submit" value="Calculate" />
			</td>
		</tr>
	</table>
</form>





<?php
/* --------------------------------------------------------------------- */
/* INFO - mortgage payment information
/* --------------------------------------------------------------------- */
?>
<?php if ($form_complete) { ?>

	<a name="total_details"></a>
	<h2>Mortgage Payment Information</h2>
	
	<table cellpadding="0" cellspacing="0" class="info">
		<tr class="<?php echo _get_background() ?>">
			<th>Down Payment:</th>
			<td>
				<?php echo _money($down_payment); ?>
			</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Amount Financed:</th>
			<td>
				<?php echo _money($financing_price); ?>
			</td>
		</tr>
		<tr class="<?php echo _get_background() ?>">
			<th>Monthly Payment:</th>
			<td>
				<?php echo _money($monthly_payment); ?>
				<span class="info">(Principal &amp; Interest ONLY)</span>
			</td>
		</tr>
		
		<?php if ($pmi_per_month) { ?>
			<tr class="pmi">
				<td colspan="2">
					<p class="info">
						Since you put less than 20% down, you will pay 
						<a href="http://www.google.com/search?hl=en&amp;q=private+mortgage+insurance">Private Mortgage Insurance</a>. 
						<acronym title="Private Mortgage Insurance">PMI</acronym> 
						tends to be about $55 per month for every $100,000 financed 
						(until you have paid off 20% of your loan). This adds 
						<strong><?php echo _money($pmi_per_month); ?></strong> 
						to your monthly payment.
					</p>
				</td>
			</tr>
		<?php } ?>
		
		<tr class="tax">
			<td colspan="2">
				<p class="info">
					Your property tax rate is <?php echo _money($property_tax_rate) ?> per $1000. 
					Your home's assessed value is <?php echo _money($assessed_value); ?>.
					This means that your yearly property taxes will be  
					<?php echo _money($property_yearly_tax); ?>, or 
					<?php echo _money($property_monthly_tax); ?> per month.
				</p>
			</td>
		</tr>
	</table>
<?php } ?>





<?php
/* --------------------------------------------------------------------- */
/* SUM - breakdown of monthly payment sum
/* --------------------------------------------------------------------- */
?>
<?php if ($form_complete) { ?>
	
	<a name="total_payment"></a>
	<h2>Your Total Monthly Payment</h2>
	<table cellpadding="0" cellspacing="0" class="sum">
		<tr>
			<td>Mortgage (Principal &amp; Interest)</td>
			<td><?php echo _money($monthly_payment); ?></td>
		</tr>
		<tr>
			<td><acronym title="Private Mortgage Insurance">PMI</acronym></td>
			<td><?php echo _money($pmi_per_month); ?></td>
		</tr>
		<tr>
			<td>Property Tax</td>
			<td><?php echo _money($property_monthly_tax); ?></td>
		</tr>
		<tr>
			<td>Condo Fee</td>
			<td><?php echo _money($condo_fee); ?></td>
		</tr>
		<tr class="total">
			<td>Total Monthly Payment</td>
			<td><?php echo _money($total_monthly_bill); ?></td>
		</tr>
	</table>
	
<?php } ?>





<?php
/* --------------------------------------------------------------------- */
/* CALCULATIONS - explanation of the calculations
/* --------------------------------------------------------------------- */
?>
<?php if ($form_complete) { ?>
	
	<h2>Calculations</h2>
	<p>
		To figure out the monthly payment, we need to know (1) how much 
		you're financing; (2) your monthly interest rate; and (3) how many 
		months you're financing for.
	</p>
	<p>
		Financials are typically quoted in yearly or annual numbers&mdash;<em>a 
		30-year mortgage or a 6% annual interest</em>. However, you pay your 
		mortgage every month. A lot of the calculations involve translating 
		those yearly numbers to their monthly equivalents.
	</p>
	<div class="calculation">
		<h3>1. Financing Price</h3>
		<p>
			First, we need to figure how much you're financing.
		</p>
		<p>
			We can do this based on the sale price of the home 
			(<strong><?php echo _money($sale_price); ?></strong>) and the 
			percent that you put down (<strong><?php echo $down_percent; ?>%</strong>).
		</p>
		<p>
			Start by calculating the down payment. Divide the percentage down by 100, 
			then multiply by the sale price of the home.
		</p>
		<p>
			(<?php echo $down_percent; ?>% / 100) x <?php echo _money($sale_price); ?> = 
			<strong><?php echo _money($down_payment); ?></strong>, 
			<em>your down payment</em>
		</p>
		<p>
			Now we can calculate how much you're financing&mdash;how much 
			you need to borrow. That's just the sale price minus your down payment.
		</p>
		<p class="result">
			<?php echo _money($sale_price); ?> - <?php echo _money($down_payment); ?> = 
			<strong><?php echo _money($financing_price); ?></strong>, 
			<em>your financing price</em>
		</p>
	</div>
	<div class="calculation">
		<h3>2. Monthly Interest Rate</h3>
		<p>
			That <strong><?php echo $mortgage_interest_percent; ?>%</strong> 
			interest rate percentage you secured is an <em>annual</em> percent.
		</p>
		<p>
			We'll need to convert that from a percentage to a decimal rate, 
			and from an annual representation to a monthly one.
		</p>
		<p>
			First, let's convert it to a decimal, by dividing the percent by 100.
		</p>
		<p>
			<?php echo $mortgage_interest_percent; ?>% / 100 = 
			<strong><?php echo $annual_interest_rate; ?></strong>, 
			<em>the annual interest rate</em>
		</p>
		<p>
			Now convert the annual rate to a monthly rate by dividing by 12 
			(for 12 months in a year).
		</p>
		<p class="result">
			<?php echo $annual_interest_rate; ?> / 12 = 
			<strong><?php echo $monthly_interest_rate; ?></strong>, 
			<em>your monthly interest rate</em>
		</p>
	</div>
	<div class="calculation">
		<h3>3. Month Term</h3>
		<p>
			Now for an easy calculation&mdash;the <strong>month term</strong>. 
			That's just the number of months you'll be paying off your loan.
		</p>
		<p class="result">
			You have a <?php echo $year_term; ?> year mortgage x 12 months = 
			<strong><?php echo $month_term; ?> months</strong>, 
			<em>your month term</em>.
		</p>
	</div>
	<div class="calculation">
		<h3>Final: Your Monthly Mortgage Payment</h3>
		<p>
			Using the three numbers above, we can now calculate your monthly payment.
		</p>
		<p>
			(financing price) x (monthly interest rate / (1 - ((1+monthly interest rate)<sup>-(monthly term)</sup>)))
		</p>
		<p class="result">
			<?php echo _money($financing_price); ?> x (<?php echo number_format($monthly_interest_rate, '4', '.', ''); ?> / (1 - ((1 + <?php echo number_format($monthly_interest_rate, '4', '.', ''); ?>)<sup>-(<?php echo $month_term; ?>)</sup>))) = <strong><?php echo _money($monthly_payment); ?></strong>, <em>your monthly payment*</em>
		</p>
		<p>
			*<em>Principal &amp; Interest only</em>. See 
			<a href="#total_payment">total monthly payment</a> for a your 
			mortgage plus taxes, insurance, and fees. See 
			<a href="#amortization">amortization</a> for a breakdown of how 
			each monthly payment is split between the bank's interest and 
			paying off the loan principal.
		</p>
	</div>
<?php } ?>





<?php
/* --------------------------------------------------------------------- */
/* AMORTIZATION - month by month breakdown of payments
/* --------------------------------------------------------------------- */
?>
<?php if ($form_complete) { ?>
	
	<?php
		// Set some base variables
		$principal	                 = $financing_price;
		$current_month               = 1;
		$current_year                = 1;
		$this_year_interest_paid     = 0;
		$this_year_principal_paid    = 0;
		$total_spent_over_term       = 0;

		// Re-figures out the monthly payment.
		$power = -($month_term);
		$denom = pow((1 + $monthly_interest_rate), $power);
		$monthly_payment = $principal * ($monthly_interest_rate / (1 - $denom));
		
		// This LEGEND will get reprinted every 12 months
		$legend  = '<tr class="legend">';
		$legend .= '<td>Month</td>';
		$legend .= '<td>Interest Paid</td>';
		$legend .= '<td>Principal Paid</td>';
		$legend .= '<td>Remaining Balance</td>';
		$legend .= '</tr>';
	?>

	<a name="amortization"></a>
	<h2>Amortization</h2>
	<p>
		Amortization for monthly payment, <?php echo _money($monthly_payment) ?>, 
		over <?= $year_term ?> years. Mortgage amortization only includes your 
		monthly principal and interest payments. Property taxes, PMI, and 
		condo fees are ignored when amortizing your mortgage.
	</p>
	<table cellpadding="0" cellspacing="0" class="amortization">
	
		<?php echo $legend; ?>
					
		<?php
			// Get the current month's payments for each month of the loan 
			while ($current_month <= $month_term) {	
				
				$interest_paid	          = $principal * $monthly_interest_rate;
				$principal_paid           = $monthly_payment - $interest_paid;
				$remaining_balance        = $principal - $principal_paid;
				$this_year_interest_paid  = $this_year_interest_paid + $interest_paid;
				$this_year_principal_paid = $this_year_principal_paid + $principal_paid;
				$show_legend              = ($current_month % 12) ? false : true;
			
				$total_spent_over_term    = $total_spent_over_term + ($interest_paid + $principal_paid);
				
				?>
		
				<tr>
					<td><?= $current_month ?></td>
					<td><?= _money($interest_paid) ?></td>
					<td><?= _money($principal_paid) ?></td>
					<td><?= _money($remaining_balance) ?></td>
				</tr>
		
				<?php if ($show_legend) { ?>
					<tr class="year_summary">
						<td colspan="4">
							<strong>Year <?php echo $current_year ?> Summary:</strong> 
							<span class="coaching">
								You spent <?php echo _money($this_year_interest_paid + $this_year_principal_paid) ?>
							</span>
							<p>
								<?php echo _money($this_year_principal_paid) ?> went to principal 
								<span class="coaching">This is equity that your building up</span>
								<br />
								
								<?php echo _money($this_year_interest_paid) ?>  went to interest 
								<span class="coaching">This is typically tax deductible</span>
							</p>
						</td>
					</tr>
					<?php
						$current_year++;
						$this_year_interest_paid  = 0;
						$this_year_principal_paid = 0;
			
						if (($current_month + 6) < $month_term) {
							echo $legend;
						}
					?>
				<?php } ?>
			
			<?php
			$principal = $remaining_balance;
			$current_month++;
		}
		?>
		<tr class="total_summary">
			<td colspan="4">
				Principal &amp; interest costs for the full  
				<?php echo $year_term ?> years of this mortgage total&hellip;
				<span class="total_spent_over_term"><?php echo _money($total_spent_over_term) ?></span>
			</td>
		</tr>	
	</table>
<?php } ?>