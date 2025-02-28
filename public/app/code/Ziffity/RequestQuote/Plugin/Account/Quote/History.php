<?php

namespace Ziffity\RequestQuote\Plugin\Account\Quote;

class History
{

	public function afterGetQuotes(\Amasty\RequestQuote\Block\Account\Quote\History $subject, $result)
	{
		$quote = $result->addFieldToFilter('amasty_quote.archive', ['neq' => 1]);
		$subject->setTemplate('Ziffity_RequestQuote::account/quote/history.phtml');
		return $quote;
	}

}
