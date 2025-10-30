using MpsMonitor.Sdk.Models.Common;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents the base class for all the requests
    /// </summary>
    /// <seealso cref="IClaimsRequest" />
    [DataContract]
    public class BaseRequest : IClaimsRequest
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="BaseRequest"/> class.
        /// </summary>
        public BaseRequest()
        {
            DealerCodes = new string[0];
            CustomerCodes = new string[0];
            Capabilities = new string[0];
        }

        /// <summary>
        /// Gets or sets the dealer codes.
        /// </summary>
        /// <value>
        /// The dealer codes.
        /// </value>
        /// <remarks>Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)</remarks>
        public string[] DealerCodes { get; set; }


        /// <summary>
        /// Gets or sets the customer codes.
        /// </summary>
        /// <value>
        /// The customer codes.
        /// </value>
        /// <remarks>Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)</remarks>
        public string[] CustomerCodes { get; set; }

        /// <summary>
        /// Gets or sets the capabilities.
        /// </summary>
        /// <value>
        /// The capabilities.
        /// </value>
        /// <remarks>Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)</remarks>
        public string[] Capabilities { get; set; }

        /// <summary>
        /// Gets or sets the role.
        /// </summary>
        /// <value>
        /// The role.
        /// </value>
        /// <remarks>Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)</remarks>
        public string Role { get; set; }

        /// <summary>
        /// Gets or sets the account identifier.
        /// </summary>
        /// <value>
        /// The account identifier.
        /// </value>
        /// <remarks>Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)</remarks>
        public int AccountId { get; set; }

        /// <summary>
        /// Gets or sets the name of the account.
        /// </summary>
        /// <value>
        /// The name of the account.
        /// </value>
        /// Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)
        public string AccountName { get; set; }

        /// <summary>
        /// Gets or sets the customer ids.
        /// </summary>
        /// <value>
        /// The customer ids.
        /// </value>
        /// Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)
        public int[] CustomerIds { get; set; }

        /// <summary>
        /// Gets or sets the dealer ids.
        /// </summary>
        /// <value>
        /// The dealer ids.
        /// </value>
        /// Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)
        public int[] DealerIds { get; set; }

        /// <summary>
        /// Gets or sets the account name autologin applicant.
        /// </summary>
        /// <value>
        /// The account name autologin applicant.
        /// </value>
        /// Non ha l'attributo DataMember perchè non va trasmesso al client (viene popolata lato server)
        public string AccountNameAutologinApplicant { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public virtual IList<CodeDesc> Validate()
        {
            return new List<CodeDesc>();
        }

    }
}