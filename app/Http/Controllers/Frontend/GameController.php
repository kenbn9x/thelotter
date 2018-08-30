<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Number;
use App\Http\Models\Frontend\Game;
use App\Http\Models\Frontend\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
	public function play( $alias )
	{
		$game              = Game::where( 'alias', $alias )->first();
		$data['game']      = $game;
		$data['pageTitle'] = __( 'label.game.buy_ticket' );
		$data['gameInfo']  = json_encode( $game->toArray() );

		return view( 'frontend.game.play', $data );
	}

	public function submitTicket( Request $request )
	{
		$data    = $request->all();
		$game    = Game::find( $request->game_id );
		$tickets = [];
		foreach ( $data['normal'] as $index => $normal ) {
			$ticket = [
				'normal' => $normal
			];
			if ( ! empty( $data['special'] ) && ! empty( $data['special'][ $index ] ) ) {
				$ticket['special'] = $data['special'][ $index ];
			}
			$tickets[] = $ticket;
		}

		$cart = session( '_cart' ) ?? [];
		if ( ! count( $cart ) ) {
			$cart[ $data['game_id'] ] = [
				'game'    => $game,
				'tickets' => $tickets,
				'cost'    => count( $tickets ) * ( $game->ticket_price )
			];
		} else if ( empty( $cart[ $data['game_id'] ] ) ) {
			$cart[ $data['game_id'] ] = [
				'game'    => $game,
				'tickets' => $tickets,
				'cost'    => count( $tickets ) * ( $game->ticket_price )
			];
		} else {
			$existedTickets = $cart[ $data['game_id'] ]['tickets'];
			foreach ( $tickets as $ticket ) {
				if ( ! Number::checkExistedTicket( $existedTickets, $ticket ) ) {
					$existedTickets[] = $ticket;
				}
			}
			$cart[ $data['game_id'] ] = [
				'game'    => $game,
				'tickets' => $existedTickets,
				'cost'    => count( $existedTickets ) * ( $game->ticket_price )
			];
		}
		session( [
			'_cart' => $cart
		] );

		return redirect()->route( 'frontend.game.cart' );
	}

	public function cart()
	{
		$games     = session( '_cart' );
		$totalCost = 0;

		if ( ! empty( $games ) ) {
			foreach ( $games as $game ) {
				$totalCost += $game['cost'];
			}
		}

		return view( 'frontend.game.cart', compact( 'games', 'totalCost' ) );
	}

	public function checkout()
	{
		$games     = session( '_cart' );
		$totalCost = 0;

		foreach ( $games as $game ) {
			$totalCost += $game['cost'];
		}

		return view( 'frontend.game.checkout', compact( 'games', 'totalCost' ) );
	}

	public function checkoutSubmit( Request $request )
	{
		$games = session( '_cart' );

		// save tickets into database
		$tickets = [];

		foreach ( $games as $game ) {
			foreach ( $game['tickets'] as $ticket ) {
				$tickets[] = [
					'game_id'         => $game['game']->id,
					'user_id'         => Auth::guard('web')->user()->id,
					'numbers'         => $ticket['normal'],
					'special_numbers' => $ticket['special'] ?? null,
					'price'           => $game['game']->ticket_price,
					'status'          => 1,
					'created_at'      => Carbon::now()
				];
			}
		}

		Ticket::insert( $tickets );

		$request->session()->forget( '_cart' );

		return view( 'frontend.game.success_checkout' );
	}
}
