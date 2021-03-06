<?
include_once('str_copycat.php');

# Копировать с доверием: если создано доверие (из N предыдущих ходов другого игрока не менее M% были отдающими), то использовать копирование с ребалансировкой длины L, иначе - копирование
class ctrStrategy_copycat_trusted extends ctrStrategy_copycat_rebalance {
	protected $trust_period;
	protected $trust_level;
	protected $wasTrustEstablished;

	protected function MakeDecision($player_side)	{
		if ($this->current_move[$player_side] == 1)	$this->wasTrustEstablished = false;

		if ($this->current_move[$player_side] <= $this->trust_period)	return ctrStrategy_copycat::MakeDecision($player_side);

		# проверить, возникло ли доверие
		if (!$this->wasTrustEstablished)	{
			for( $pos_moves = 0, $n = 1; $n <= $this->trust_period; $n++ )	{
				if ($this->getOtherSideMove($player_side, $n) == 1)	$pos_moves++;
			}

			if ($pos_moves >= $this->trust_period * $this->trust_level / 100.0)
				$this->wasTrustEstablished = true;
		}


		return ($this->wasTrustEstablished) ? ctrStrategy_copycat_rebalance::MakeDecision($player_side) : ctrStrategy_copycat::MakeDecision($player_side);
	}

	function setParam($param = null)	{
		if (is_null($param))	{
			$this->trust_period	= 5;
			$this->trust_level	= 80;

			return parent::setParam($param);
		}

		if (is_numeric($param))	{
			$m = array();
			if (!preg_match('|(\d+);(\d+);(\d+)|', $param, $m))	return false;

			$this->trust_period	= $m[1];
			$this->trust_level	= $m[2];

			return parent::setParam($m[3]);
		}

		return false;
	}

	function getColor()	{
		$r =  37;
		$g = 202;
		$b = 224;

		$d = ($this->trust_period > 0) ? round(log($this->trust_period, 2), 0) : 0;

		$dr = 1;
		$dg = 3;
		$db = 3;

		$fr = $r + $d * $dr;
		$fg = $g + $d * $dg;
		$fb = $b + $d * $db;


		return strval(substr('0'.dechex($fr), -2, 2) . substr('0'.dechex($fg), -2, 2) . substr('0'.dechex($fb), -2, 2));
	}
};
