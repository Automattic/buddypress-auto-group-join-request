<?php
/**
 * Integration tests for sending group join requests.
 *
 * @package Automattic\BuddyPressAutoGroupJoinRequest\Tests\Integration
 */

namespace Automattic\BuddyPressAutoGroupJoinRequest\Tests\Integration;

use function Automattic\BuddyPressAutoGroupJoinRequest\bp_auto_group_join_get_config;

/**
 * Checks that a profile update sends, or doesn't send, a join request against a real BuddyPress.
 */
class JoinRequestTest extends TestCase {

	/**
	 * ID of the profile field the configuration checks.
	 *
	 * @var int
	 */
	private $field_id;

	/**
	 * ID of the private group users are asked to join.
	 *
	 * @var int
	 */
	private $group_id;

	/**
	 * Create a profile field and a private group, and map one value of the field to the group.
	 */
	public function set_up() {
		parent::set_up();

		$field_group_id = xprofile_insert_field_group( array( 'name' => 'Membership' ) );
		$this->field_id = xprofile_insert_field(
			array(
				'field_group_id' => $field_group_id,
				'name'           => 'Profile Type',
				'type'           => 'textbox',
			)
		);

		$this->group_id = groups_create_group(
			array(
				'creator_id' => self::factory()->user->create(),
				'name'       => 'Partners',
				'slug'       => 'partners',
				'status'     => 'private',
			)
		);

		$group_id = $this->group_id;
		add_filter(
			'bp_auto_group_join_config',
			static function ( $config ) use ( $group_id ) {
				$config['partners'] = array(
					'profile_field_name'  => 'Profile Type',
					'profile_field_value' => 'Partner',
					'group_id'            => $group_id,
				);

				return $config;
			}
		);
	}

	/**
	 * Create a user and save a value in the mapped profile field.
	 *
	 * @param string $value Profile field value.
	 * @return int User ID.
	 */
	private function create_user_with_profile_type( $value ) {
		$user_id = self::factory()->user->create();
		xprofile_set_field_data( $this->field_id, $user_id, $value );

		return $user_id;
	}

	/**
	 * Count the user's join requests for the group.
	 *
	 * @param int $user_id User ID.
	 * @return int Number of requests.
	 */
	private function count_requests( $user_id ) {
		return count(
			groups_get_requests(
				array(
					'user_id' => $user_id,
					'item_id' => $this->group_id,
					'fields'  => 'ids',
				)
			)
		);
	}

	/**
	 * A user whose profile matches the configured value is asked to join the group.
	 */
	public function test_matching_profile_value_sends_join_request(): void {
		$user_id = $this->create_user_with_profile_type( 'Partner' );

		do_action( 'xprofile_updated_profile', $user_id );

		$this->assertSame( 1, $this->count_requests( $user_id ) );
		$this->assertFalse( groups_is_user_member( $user_id, $this->group_id ), 'A request should not make the user a member.' );
	}

	/**
	 * A user whose profile doesn't match is left alone.
	 */
	public function test_other_profile_value_sends_no_request(): void {
		$user_id = $this->create_user_with_profile_type( 'Student' );

		do_action( 'xprofile_updated_profile', $user_id );

		$this->assertSame( 0, $this->count_requests( $user_id ) );
	}

	/**
	 * A user who is already in the group isn't asked to join it again.
	 */
	public function test_existing_member_gets_no_request(): void {
		$user_id = $this->create_user_with_profile_type( 'Partner' );
		groups_join_group( $this->group_id, $user_id );

		do_action( 'xprofile_updated_profile', $user_id );

		$this->assertSame( 0, $this->count_requests( $user_id ) );
	}

	/**
	 * Updating the profile again doesn't send a second request while the first is pending.
	 */
	public function test_pending_request_is_not_duplicated(): void {
		$user_id = $this->create_user_with_profile_type( 'Partner' );

		do_action( 'xprofile_updated_profile', $user_id );
		do_action( 'xprofile_updated_profile', $user_id );

		$this->assertSame( 1, $this->count_requests( $user_id ) );
	}

	/**
	 * A configuration missing a required key is dropped, and the valid ones are kept.
	 */
	public function test_incomplete_configuration_is_dropped(): void {
		add_filter(
			'bp_auto_group_join_config',
			static function ( $config ) {
				$config['incomplete'] = array( 'profile_field_name' => 'Profile Type' );

				return $config;
			}
		);

		$this->assertSame( array( 'partners' ), array_keys( bp_auto_group_join_get_config() ) );
	}
}
