using MpsMonitor.Sdk.Models.Common;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Requests
{
    /// <summary>
    /// Represents a reuqest to retrieve a pagedlist  result
    /// </summary>
    /// <seealso cref="BaseRequest" />
    [DataContract]
    public class PagedRequest : BaseRequest
    {
        public PagedRequest()
        {
            this.PageNumber = 1;
            this.PageRows = 20;
            this.SortColumn = "Id";
            this.SortOrder = SortDirectionEnum.Asc;
        }

        /// <summary>
        /// Gets or sets the page number.
        /// </summary>
        /// <value>
        /// The page number.
        /// </value>
        [DataMember]
        [Required]
        public int PageNumber { get; set; }

        /// <summary>
        /// Gets or sets the page rows.
        /// </summary>
        /// <value>
        /// The page rows.
        /// </value>
        [DataMember]
        [Required]
        public int PageRows { get; set; }

        /// <summary>
        /// Gets or sets the sort column.
        /// </summary>
        /// <value>
        /// The sort column.
        /// </value>
        [DataMember]
        [Required]
        public string SortColumn { get; set; }

        /// <summary>
        /// Gets or sets the sort order.
        /// </summary>
        /// <value>
        /// The sort order.
        /// </value>
        [DataMember]
        [Required]
        public SortDirectionEnum SortOrder { get; set; }

        /// <summary>
        /// Validates the request
        /// </summary>
        /// <returns></returns>
        public override IList<CodeDesc> Validate()
        {
            var errors = base.Validate();
            //if (PageNumber <= 0)
            //{
            //    errors.Add(new CodeDesc("PageNumber", "PageNumber must be greater than 0"));
            //}

            if (PageRows <= 0)
            {
                errors.Add(new CodeDesc("PageRows", "PageRows must be greater than 0"));
            }

            if (string.IsNullOrEmpty(SortColumn))
            {
                errors.Add(new CodeDesc("SortColumn", "SortColumn cannot be null or empty"));
            }

            return errors;
        }

        /// <summary>
        /// The sort direction
        /// </summary>
        public enum SortDirectionEnum
        {
            /// <summary>
            /// The ascending direction
            /// </summary>
            Asc,
            /// <summary>
            /// The descending direction
            /// </summary>
            Desc
        }
    }
}