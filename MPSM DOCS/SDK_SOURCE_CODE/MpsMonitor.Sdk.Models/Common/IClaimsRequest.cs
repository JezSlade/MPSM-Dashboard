namespace MpsMonitor.Sdk.Models.Common
{
    /// <summary>
    /// Represent a request with claims info
    /// </summary>
    public interface IClaimsRequest
    {
        /// <summary>
        /// Gets or sets the dealer codes.
        /// </summary>
        /// <value>
        /// The dealer codes.
        /// </value>
        string[] DealerCodes { get; set; }

        /// <summary>
        /// Gets or sets the customer codes.
        /// </summary>
        /// <value>
        /// The customer codes.
        /// </value>
        string[] CustomerCodes { get; set; }

        /// <summary>
        /// Gets or sets the capabilities.
        /// </summary>
        /// <value>
        /// The capabilities.
        /// </value>
        string[] Capabilities { get; set; }

        /// <summary>
        /// Gets or sets the role.
        /// </summary>
        /// <value>
        /// The role.
        /// </value>
        string Role { get; set; }

        /// <summary>
        /// Gets or sets the account identifier.
        /// </summary>
        /// <value>
        /// The account identifier.
        /// </value>
        int AccountId { get; set; }

        /// <summary>
        /// Gets or sets the name of the account.
        /// </summary>
        /// <value>
        /// The name of the account.
        /// </value>
        string AccountName { get; set; }

        /// <summary>
        /// Gets or sets the customer ids.
        /// </summary>
        /// <value>
        /// The customer ids.
        /// </value>
        int[] CustomerIds { get; set; }

        /// <summary>
        /// Gets or sets the dealer ids.
        /// </summary>
        /// <value>
        /// The dealer ids.
        /// </value>
        int[] DealerIds { get; set; }

        /// <summary>
        /// Gets or sets the account name autologin applicant.
        /// </summary>
        /// <value>
        /// The account name autologin applicant.
        /// </value>
        string AccountNameAutologinApplicant { get; set; }
    }
}